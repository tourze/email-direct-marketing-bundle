<?php

namespace EmailDirectMarketingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\Symfony\Async\Attribute\Async;

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

    #[Async]
    public function createQueue(Task $task): void
    {
        if (empty($task->getTags())) {
            $this->logger->warning("任务[{$task->getId()}]没设置标签，不能继续发送", [
                'task' => $task,
            ]);

            return;
        }
        if ($task->getSenders()->isEmpty()) {
            $this->logger->warning("任务[{$task->getId()}]没设置发送邮箱，不能继续发送", [
                'task' => $task,
            ]);

            return;
        }

        // 查找符合条件的所有用户
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
            ->where("a.unsubscribed != true AND ({$tagParts})");
        foreach ($tagParams as $param => $tagParam) {
            $qb->setParameter($param, $tagParam);
        }

        $result = $qb->getQuery()
            ->setHint(Query::HINT_READ_ONLY, true)
            ->toIterable();
        $senderCount = $task->getSenders()->count();
        foreach ($result as $item) {
            /** @var Receiver $item */
            $queue = new Queue();
            $queue->setTask($task);
            $queue->setReceiver($item);

            // 按照设计，下面的 subject 和 email 应该有变量支持替换才对
            $queue->setEmailSubject(
                $this->formatText($task->getTemplate()->getSubject(), $task, $item),
            );
            $queue->setEmailBody(
                $this->formatText($task->getTemplate()->getHtmlBody(), $task, $item)
            );

            $sender = $task->getSenders()->get(rand(0, $senderCount - 1));
            $queue->setSender($sender);
            $queue->setDone(false);
            $queue->setValid(true);

            $this->entityManager->persist($queue);
            $this->entityManager->flush();

            $nextMessage = new SendQueueEmailMessage();
            $nextMessage->setQueueId($queue->getId());
            $this->messageBus->dispatch($nextMessage);
        }
    }

    /**
     * 替换变量，写法如 ${receiver.getEmailAddress()}
     */
    private function formatText(string $text, Task $task, Receiver $receiver): string
    {
        preg_match_all('@\$\{(.*?)\}@', $text, $matches);
        foreach ($matches[0] as $key => $search) {
            $expression = $matches[1][$key];
            $replace = $this->expressionLanguage->evaluate($expression, [
                'receiver' => $receiver,
                'task' => $task,
                'text' => $text,
            ]);
            $text = str_replace($search, $replace, $text);
        }

        return $text;
    }
}
