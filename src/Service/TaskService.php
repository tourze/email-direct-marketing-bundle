<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Exception\TaskTemplateException;
use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;

#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'email_direct_marketing')]
class TaskService
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ReceiverRepository $receiverRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly QueueRepository $queueRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * 创建任务发送队列
     */
    #[Async]
    public function createQueue(Task $task): void
    {
        if (!$this->validateTask($task)) {
            return;
        }

        try {
            $qb = $this->buildReceiverQuery($task);
            $totalCount = $this->getTotalCount($qb, $task);

            if (0 === $totalCount) {
                $this->logger->info("任务[{$task->getId()}]没有匹配的收件人", [
                    'task' => $task,
                    'tags' => $task->getTags(),
                ]);
                $this->finishTask($task, 0, 0, 0);

                return;
            }

            $this->processQueueCreation($qb, $task, $totalCount);
        } catch (\Throwable $e) {
            $this->logger->error("任务[{$task->getId()}]创建队列失败: " . $e->getMessage(), [
                'task' => $task,
                'exception' => $e,
            ]);
            $this->finishTask($task, 0, 0, 0);
        }
    }

    /**
     * 完成任务
     */
    public function finishTask(Task $task, int $totalCount, int $successCount, int $failureCount): void
    {
        $task->setStatus(TaskStatus::FINISHED);
        $task->setTotalCount($totalCount);
        $task->setSuccessCount($successCount);
        $task->setFailureCount($failureCount);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->logger->info("任务[{$task->getId()}]已完成", [
            'task' => $task,
            'totalCount' => $totalCount,
            'successCount' => $successCount,
            'failureCount' => $failureCount,
        ]);
    }

    /**
     * 更新任务统计信息
     */
    public function updateTaskStatistics(Task $task): void
    {
        $successCount = $this->queueRepository->count([
            'task' => $task,
            'done' => true,
            'errorMessage' => null,
        ]);

        $failureCount = $this->queueRepository->count([
            'task' => $task,
            'done' => true,
            'errorMessage' => ['<>' => null],
        ]);

        $pendingCount = $this->queueRepository->count([
            'task' => $task,
            'done' => false,
        ]);

        $task->setSuccessCount($successCount);
        $task->setFailureCount($failureCount);

        // 如果没有待处理的队列，标记任务为已完成
        if (0 === $pendingCount) {
            $task->setStatus(TaskStatus::FINISHED);

            $this->logger->info("任务[{$task->getId()}]已完成", [
                'task' => $task,
                'successCount' => $successCount,
                'failureCount' => $failureCount,
            ]);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    /**
     * 替换变量，写法如 ${receiver.getEmailAddress()}
     */
    private function formatText(string $text, Task $task, Receiver $receiver): string
    {
        try {
            preg_match_all('@\$\{(.*?)\}@', $text, $matches);
            foreach ($matches[0] as $key => $search) {
                $expression = $matches[1][$key];
                $replace = $this->expressionLanguage->evaluate($expression, [
                    'receiver' => $receiver,
                    'task' => $task,
                    'text' => $text,
                    'now' => new \DateTimeImmutable(),
                ]);
                $text = str_replace($search, $replace, $text);
            }
        } catch (\Throwable $e) {
            $this->logger->error('变量替换失败: ' . $e->getMessage(), [
                'task' => $task,
                'receiver' => $receiver,
                'exception' => $e,
            ]);
        }

        return $text;
    }

    /**
     * 验证任务是否可以执行
     */
    private function validateTask(Task $task): bool
    {
        if ([] === $task->getTags()) {
            $this->logger->warning("任务[{$task->getId()}]没设置标签，不能继续发送", [
                'task' => $task,
            ]);
            $this->finishTask($task, 0, 0, 0);

            return false;
        }

        if ($task->getSenders()->isEmpty()) {
            $this->logger->warning("任务[{$task->getId()}]没设置发送邮箱，不能继续发送", [
                'task' => $task,
            ]);
            $this->finishTask($task, 0, 0, 0);

            return false;
        }

        return true;
    }

    /**
     * 构建接收者查询
     */
    private function buildReceiverQuery(Task $task): QueryBuilder
    {
        $tagParts = [];
        $tagParams = [];
        foreach ($task->getTags() as $i => $tag) {
            $tag = strval($tag);
            $tagParts[] = "JSON_SEARCH(a.tags, 'one', :keyword_{$i}) IS NOT NULL";
            $tagParams["keyword_{$i}"] = $tag;
        }
        $tagParts = implode(' OR ', $tagParts);

        $qb = $this->receiverRepository
            ->createQueryBuilder('a')
            ->where("a.unsubscribed != true AND ({$tagParts})")
        ;

        foreach ($tagParams as $param => $tagParam) {
            $qb->setParameter($param, $tagParam);
        }

        return $qb;
    }

    /**
     * 获取总数并更新任务
     * @param mixed $qb
     */
    private function getTotalCount($qb, Task $task): int
    {
        $count = clone $qb;
        $totalCount = $count->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
        $task->setTotalCount($totalCount);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $totalCount;
    }

    /**
     * 处理队列创建
     * @param mixed $qb
     */
    private function processQueueCreation($qb, Task $task, int $totalCount): void
    {
        $result = $qb->getQuery()
            ->setHint(Query::HINT_READ_ONLY, true)
            ->toIterable()
        ;

        $senderCount = $task->getSenders()->count();
        $queueCount = 0;

        $this->logger->info("任务[{$task->getId()}]开始创建队列，预计发送{$totalCount}个邮件", [
            'task' => $task,
            'totalCount' => $totalCount,
        ]);

        foreach ($result as $item) {
            /** @var Receiver $item */
            $queue = $this->createQueueItem($task, $item, $senderCount);
            $this->entityManager->persist($queue);

            // 每50条提交一次，避免内存占用过大
            if (0 === ++$queueCount % 50) {
                $this->entityManager->flush();
                $this->logger->debug("任务[{$task->getId()}]已创建{$queueCount}/{$totalCount}个队列");
            }

            $nextMessage = new SendQueueEmailMessage();
            $nextMessage->setQueueId($queue->getId());
            $this->messageBus->dispatch($nextMessage);
        }

        // 最终提交
        $this->entityManager->flush();

        $this->logger->info("任务[{$task->getId()}]队列创建完成，共{$queueCount}条", [
            'task' => $task,
            'queueCount' => $queueCount,
        ]);
    }

    /**
     * 创建队列项
     */
    private function createQueueItem(Task $task, Receiver $receiver, int $senderCount): Queue
    {
        $queue = new Queue();
        $queue->setTask($task);
        $queue->setReceiver($receiver);

        // 处理变量替换
        $template = $task->getTemplate();
        if (null === $template) {
            throw new TaskTemplateException('任务模板不能为null');
        }

        $templateSubject = $template->getSubject();
        $templateHtmlBody = $template->getHtmlBody();

        if (null === $templateSubject || null === $templateHtmlBody) {
            throw new TaskTemplateException('模板主题和内容不能为null');
        }

        $queue->setEmailSubject(
            $this->formatText($templateSubject, $task, $receiver),
        );
        $queue->setEmailBody(
            $this->formatText($templateHtmlBody, $task, $receiver)
        );

        $sender = $task->getSenders()->get(rand(0, $senderCount - 1));
        $queue->setSender($sender);
        $queue->setDone(false);
        $queue->setValid(true);

        return $queue;
    }
}
