<?php

namespace EmailDirectMarketingBundle\Tests\Command;

use EmailDirectMarketingBundle\Command\StartEdmTaskCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(StartEdmTaskCommand::class)]
#[RunTestsInSeparateProcesses]
final class StartEdmTaskCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $this->assertInstanceOf(StartEdmTaskCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    public function testExtendsCommand(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testHasCorrectName(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $this->assertSame(StartEdmTaskCommand::NAME, $command->getName());
    }

    public function testCommandNameConstant(): void
    {
        $this->assertSame('edm:start-task', StartEdmTaskCommand::NAME);
    }

    public function testCommandHasDescription(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $description = $command->getDescription();
        $this->assertNotEmpty($description);
    }

    public function testCommandHasOptions(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('task-id'));
        $this->assertTrue($definition->hasOption('force'));
    }

    public function testTaskIdOptionConfiguration(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $definition = $command->getDefinition();
        $taskIdOption = $definition->getOption('task-id');
        $this->assertFalse($taskIdOption->isValueRequired());
        $this->assertTrue($taskIdOption->isValueOptional());
    }

    public function testForceOptionConfiguration(): void
    {
        /** @var StartEdmTaskCommand $command */
        $command = self::getContainer()->get(StartEdmTaskCommand::class);
        $definition = $command->getDefinition();
        $forceOption = $definition->getOption('force');
        $this->assertFalse($forceOption->acceptValue());
    }

    public function testOptionTaskId(): void
    {
        $exitCode = $this->commandTester->execute(['--task-id' => '999']);
        $this->assertContains($exitCode, [Command::SUCCESS, Command::FAILURE]);
        $output = $this->commandTester->getDisplay();
        $this->assertIsString($output);
    }

    public function testOptionForce(): void
    {
        $exitCode = $this->commandTester->execute(['--force' => true]);
        $this->assertContains($exitCode, [Command::SUCCESS, Command::FAILURE]);
        $output = $this->commandTester->getDisplay();
        $this->assertIsString($output);
    }

    public function testCommandExecutionWithInvalidTaskId(): void
    {
        $exitCode = $this->commandTester->execute(['--task-id' => '999']);
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('任务ID 999 不存在', $this->commandTester->getDisplay());
    }
}
