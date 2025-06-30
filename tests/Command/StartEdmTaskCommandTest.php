<?php

namespace EmailDirectMarketingBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Command\StartEdmTaskCommand;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use EmailDirectMarketingBundle\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartEdmTaskCommandTest extends TestCase
{
    private StartEdmTaskCommand $command;
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;
    private TaskService $taskService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskService = $this->createMock(TaskService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new StartEdmTaskCommand(
            $this->taskRepository,
            $this->entityManager,
            $this->taskService,
            $this->logger
        );
    }

    public function testExtendsCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testHasCorrectName(): void
    {
        $this->assertSame(StartEdmTaskCommand::NAME, $this->command->getName());
    }

    public function testCommandNameConstant(): void
    {
        $this->assertSame('edm:start-task', StartEdmTaskCommand::NAME);
    }

    public function testCommandHasDescription(): void
    {
        $description = $this->command->getDescription();
        
        $this->assertNotEmpty($description);
    }

    public function testCommandHasOptions(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasOption('task-id'));
        $this->assertTrue($definition->hasOption('force'));
    }

    public function testTaskIdOptionConfiguration(): void
    {
        $definition = $this->command->getDefinition();
        $taskIdOption = $definition->getOption('task-id');
        
        $this->assertFalse($taskIdOption->isValueRequired());
        $this->assertTrue($taskIdOption->isValueOptional());
    }

    public function testForceOptionConfiguration(): void
    {
        $definition = $this->command->getDefinition();
        $forceOption = $definition->getOption('force');
        
        $this->assertFalse($forceOption->acceptValue());
    }
} 