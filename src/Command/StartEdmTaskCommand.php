<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use EmailDirectMarketingBundle\Service\TaskService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(
    name: self::NAME,
    description: 'EDM检查并开始任务'
)]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'email_direct_marketing')]
class StartEdmTaskCommand extends Command
{
    public const NAME = 'edm:start-task';

    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskService $taskService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('task-id', null, InputOption::VALUE_OPTIONAL, '指定任务ID', null)
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制启动任务，忽略开始时间检查')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $taskId = $input->getOption('task-id');
        $force = $input->getOption('force');

        try {
            if (null !== $taskId) {
                return $this->executeForSingleTask((int) $taskId, $force, $io);
            }

            return $this->executeForAllTasks($io);
        } catch (\Throwable $e) {
            $this->logger->error('执行任务时发生错误: ' . $e->getMessage(), [
                'exception' => $e,
                'taskId' => $taskId,
            ]);

            $io->error('执行任务时发生错误: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function executeForSingleTask(int $taskId, bool $force, SymfonyStyle $io): int
    {
        $task = $this->taskRepository->find($taskId);
        if (null === $task) {
            $io->error(sprintf('任务ID %d 不存在', $taskId));

            return Command::FAILURE;
        }

        if (null === $task->isValid() || !$task->isValid()) {
            $io->warning(sprintf('任务ID %d 未启用', $taskId));

            return Command::FAILURE;
        }

        if (TaskStatus::WAITING !== $task->getStatus()) {
            $io->warning(sprintf('任务ID %d 状态不是等待发送', $taskId));

            return Command::FAILURE;
        }

        if (!$force && $task->getStartTime() > new \DateTimeImmutable()) {
            $io->warning(sprintf('任务ID %d 的开始时间是 %s，还未到发送时间',
                $taskId,
                $task->getStartTime()->format('Y-m-d H:i:s')
            ));

            return Command::FAILURE;
        }

        $this->processTask($task, $io);

        return Command::SUCCESS;
    }

    private function executeForAllTasks(SymfonyStyle $io): int
    {
        $now = new \DateTimeImmutable();

        /** @var Task[] $tasks */
        $tasks = $this->taskRepository->createQueryBuilder('a')
            ->where('a.valid = true AND a.status = :status AND a.startTime < :now')
            ->setParameter('status', TaskStatus::WAITING)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult()
        ;

        if ([] === $tasks) {
            $io->info('没有需要处理的任务');

            return Command::SUCCESS;
        }

        $io->info(sprintf('找到 %d 个需要处理的任务', count($tasks)));

        foreach ($tasks as $task) {
            $this->processTask($task, $io);
        }

        return Command::SUCCESS;
    }

    /**
     * 处理单个任务
     */
    private function processTask(Task $task, SymfonyStyle $io): void
    {
        $io->section(sprintf('处理任务 #%d: %s', $task->getId(), $task->getTitle()));

        try {
            $task->setStatus(TaskStatus::SENDING);
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->info('任务已更新为发送中状态，开始创建队列...');
            $this->taskService->createQueue($task);
            $io->success('任务处理完成');
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('处理任务 #%d 失败: %s', $task->getId(), $e->getMessage()), [
                'task' => $task,
                'exception' => $e,
            ]);

            $io->error(sprintf('处理任务失败: %s', $e->getMessage()));

            // 尝试将任务重置为等待状态
            try {
                $task->setStatus(TaskStatus::WAITING);
                $this->entityManager->persist($task);
                $this->entityManager->flush();
                $io->note('任务已重置为等待状态');
            } catch (\Exception $resetEx) {
                $this->logger->error(sprintf('重置任务 #%d 状态失败: %s', $task->getId(), $resetEx->getMessage()), [
                    'task' => $task,
                    'exception' => $resetEx,
                ]);
            }
        }
    }
}
