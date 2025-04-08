<?php

namespace EmailDirectMarketingBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use EmailDirectMarketingBundle\Service\TaskService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask('* * * * *')]
#[AsCommand(name: 'edm:start-task', description: 'EDM检查并开始任务')]
class StartEdmTaskCommand extends Command
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskService $taskService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Task[] $tasks */
        $tasks = $this->taskRepository->createQueryBuilder('a')
            ->where('a.valid = true AND a.status = :status AND a.startTime < :now')
            ->setParameter('status', TaskStatus::WAITING)
            ->setParameter('now', Carbon::now())
            ->getQuery()
            ->getResult();
        foreach ($tasks as $task) {
            $task->setStatus(TaskStatus::SENDING);
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->taskService->createQueue($task);
        }

        return Command::SUCCESS;
    }
}
