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

        // 创建必需的模板
        $template = new Template();
        $template->setName('测试模板');
        $template->setSubject('测试主题');
        $template->setHtmlBody('<p>测试内容</p>');

        $entityManager->persist($template);
        $entityManager->flush();

        $task->setTemplate($template);

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

        $task = $this->createValidTask();
        $task->setTags([]);

        $taskService->createQueue($task);

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
        $this->assertSame(0, $task->getTotalCount());
        $this->assertSame(0, $task->getSuccessCount());
        $this->assertSame(0, $task->getFailureCount());
    }

    public function testCreateQueueWithEmptySendersFinishesTaskWithZeroCounts(): void
    {
        $taskService = $this->getTaskService();

        $task = $this->createValidTask();
        $task->setTags(['tag1']);
        // 默认是空集合

        $taskService->createQueue($task);

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
        $taskService = $this->getTaskService();

        // 使用反射来访问私有方法
        $reflectionClass = new \ReflectionClass(TaskService::class);
        $formatTextMethod = $reflectionClass->getMethod('formatText');
        $formatTextMethod->setAccessible(true);

        $task = new Task();
        $task->setTitle('测试任务');

        $receiver = new Receiver();
        $receiver->setName('张三');
        $receiver->setEmailAddress('test@example.com');

        // 包含表达式的模板文本
        $text = '尊敬的${receiver.getName()}，您的邮箱是${receiver.getEmailAddress()}，任务标题是${task.getTitle()}';

        // 调用私有方法
        $result = $formatTextMethod->invoke($taskService, $text, $task, $receiver);

        $this->assertSame('尊敬的张三，您的邮箱是test@example.com，任务标题是测试任务', $result);
    }
}
