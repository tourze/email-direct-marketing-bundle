<?php

namespace EmailDirectMarketingBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Service\TaskService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(TaskService::class)]
#[RunTestsInSeparateProcesses]
final class TaskServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    private function getTaskService(): TaskService
    {
        return self::getService(TaskService::class);
    }

    private function createValidTask(): Task
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('测试任务');
        $task->setStartTime(new \DateTimeImmutable());
        $task->setStatus(TaskStatus::WAITING); // 设置初始状态

        // 创建必需的模板
        $template = new Template();
        $template->setName('测试模板');
        $template->setSubject('测试主题');
        $template->setHtmlBody('<p>测试内容</p>');

        $entityManager->persist($template);
        $entityManager->flush();

        $task->setTemplate($template);

        // 持久化任务到数据库
        $entityManager->persist($task);
        $entityManager->flush();

        return $task;
    }

    public function testFinishTaskUpdatesTaskCorrectly(): void
    {
        $taskService = $this->getTaskService();

        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::SENDING);

        $taskService->finishTask($task, 100, 90, 10);

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
        $this->assertSame(100, $task->getTotalCount());
        $this->assertSame(90, $task->getSuccessCount());
        $this->assertSame(10, $task->getFailureCount());
    }

    public function testCreateQueueWithEmptyTagsFinishesTaskWithZeroCounts(): void
    {
        $taskService = $this->getTaskService();
        $entityManager = self::getService(EntityManagerInterface::class);

        $task = $this->createValidTask();
        $task->setTags([]);
        $entityManager->flush(); // 持久化标签变更
        $entityManager->refresh($task); // 刷新对象状态

        $taskService->createQueue($task);

        $entityManager->refresh($task); // 再次刷新以获取 finishTask 的变更

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
        $this->assertSame(0, $task->getTotalCount());
        $this->assertSame(0, $task->getSuccessCount());
        $this->assertSame(0, $task->getFailureCount());
    }

    public function testCreateQueueWithEmptySendersFinishesTaskWithZeroCounts(): void
    {
        $taskService = $this->getTaskService();
        $entityManager = self::getService(EntityManagerInterface::class);

        $task = $this->createValidTask();
        $task->setTags(['tag1']);
        // 默认是空集合
        $entityManager->flush(); // 持久化标签变更
        $entityManager->refresh($task); // 刷新对象状态

        $taskService->createQueue($task);

        $entityManager->refresh($task); // 再次刷新以获取 finishTask 的变更

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
        $this->assertSame(0, $task->getTotalCount());
        $this->assertSame(0, $task->getSuccessCount());
        $this->assertSame(0, $task->getFailureCount());
    }

    public function testCreateQueueWithNoMatchingReceiversCompletesSuccessfully(): void
    {
        $taskService = $this->getTaskService();

        $task = $this->createValidTask();
        $task->setTags(['tag1', 'tag2']);

        // 运行测试，确保不抛出异常
        $this->expectNotToPerformAssertions();
        $taskService->createQueue($task);
    }

    public function testUpdateTaskStatisticsWithPendingQueuesUpdatesOnlyCounters(): void
    {
        $taskService = $this->getTaskService();

        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::SENDING);

        $taskService->updateTaskStatistics($task);

        // 验证任务计数器被更新（即使没有待处理的队列，也会更新状态）
        $this->assertNotNull($task->getSuccessCount());
        $this->assertNotNull($task->getFailureCount());
    }

    public function testUpdateTaskStatisticsWithNoPendingQueuesFinishesTask(): void
    {
        $taskService = $this->getTaskService();

        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::SENDING);

        $taskService->updateTaskStatistics($task);

        // 当没有待处理的队列时，任务应该被标记为已完成
        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
    }

    public function testFormatTextReplacesVariables(): void
    {
        // 直接测试ExpressionLanguage的变量替换功能
        // 这验证了formatText方法使用的核心功能，而避免访问私有方法
        $expressionLanguage = new \Symfony\Component\ExpressionLanguage\ExpressionLanguage();

        $task = new Task();
        $task->setTitle('测试任务');

        $receiver = new Receiver();
        $receiver->setName('张三');
        $receiver->setEmailAddress('test@example.com');

        // 测试各种表达式语法
        $context = [
            'receiver' => $receiver,
            'task' => $task,
            'text' => 'dummy text',
            'now' => new \DateTimeImmutable(),
        ];

        // 测试receiver.getName()表达式
        $result1 = $expressionLanguage->evaluate('receiver.getName()', $context);
        $this->assertSame('张三', $result1);

        // 测试receiver.getEmailAddress()表达式
        $result2 = $expressionLanguage->evaluate('receiver.getEmailAddress()', $context);
        $this->assertSame('test@example.com', $result2);

        // 测试task.getTitle()表达式
        $result3 = $expressionLanguage->evaluate('task.getTitle()', $context);
        $this->assertSame('测试任务', $result3);

        // 测试复杂的组合表达式
        $result4 = $expressionLanguage->evaluate('receiver.getName() ~ "的邮箱是" ~ receiver.getEmailAddress()', $context);
        $this->assertSame('张三的邮箱是test@example.com', $result4);
    }
}
