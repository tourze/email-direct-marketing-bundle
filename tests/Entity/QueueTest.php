<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Queue::class)]
final class QueueTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Queue();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'emailSubject' => ['emailSubject', 'Test Subject'];
        yield 'emailBody' => ['emailBody', '<p>Test Body</p>'];
        yield 'done' => ['done', true];
        yield 'valid' => ['valid', true];
    }

    public function testQueueCreation(): void
    {
        $queue = new Queue();

        // 测试初始状态
        $this->assertSame(0, $queue->getId());
        $this->assertNull($queue->getTask());
        $this->assertNull($queue->getSender());
        $this->assertNull($queue->getReceiver());
        $this->assertNull($queue->getEmailSubject());
        $this->assertNull($queue->getEmailBody());
        $this->assertNull($queue->getErrorMessage());
        $this->assertNull($queue->isDone());
        $this->assertFalse($queue->isValid());
        $this->assertNull($queue->getCreateTime());
        $this->assertNull($queue->getUpdateTime());
    }

    public function testInitialState(): void
    {
        $queue = new Queue();

        $this->assertSame(0, $queue->getId());
        $this->assertNull($queue->getTask());
        $this->assertNull($queue->getSender());
        $this->assertNull($queue->getReceiver());
        $this->assertNull($queue->getEmailSubject());
        $this->assertNull($queue->getEmailBody());
        $this->assertNull($queue->getErrorMessage());
        $this->assertNull($queue->isDone());
        $this->assertFalse($queue->isValid());
        $this->assertNull($queue->getCreateTime());
        $this->assertNull($queue->getUpdateTime());
    }

    public function testSetTaskGetTask(): void
    {
        $queue = new Queue();

        // 使用具体Entity类进行Mock，因为测试的是setter/getter的基本功能
        // Entity之间的关联关系不需要真实的业务逻辑，Mock对象足够验证属性设置
        $task = $this->createMock(Task::class);
        $queue->setTask($task);
        $this->assertSame($task, $queue->getTask());
    }

    public function testSetSenderGetSender(): void
    {
        $queue = new Queue();

        // 使用具体Entity类进行Mock，因为测试的是setter/getter的基本功能
        // Entity之间的关联关系不需要真实的业务逻辑，Mock对象足够验证属性设置
        $sender = $this->createMock(Sender::class);
        $queue->setSender($sender);
        $this->assertSame($sender, $queue->getSender());
    }

    public function testSetReceiverGetReceiver(): void
    {
        $queue = new Queue();

        // 使用具体Entity类进行Mock，因为测试的是setter/getter的基本功能
        // Entity之间的关联关系不需要真实的业务逻辑，Mock对象足够验证属性设置
        $receiver = $this->createMock(Receiver::class);
        $queue->setReceiver($receiver);
        $this->assertSame($receiver, $queue->getReceiver());
    }

    public function testSetEmailSubjectGetEmailSubject(): void
    {
        $queue = new Queue();

        $subject = '测试邮件标题';
        $queue->setEmailSubject($subject);
        $this->assertSame($subject, $queue->getEmailSubject());
    }

    public function testSetEmailBodyGetEmailBody(): void
    {
        $queue = new Queue();

        $body = '<p>测试邮件内容</p>';
        $queue->setEmailBody($body);
        $this->assertSame($body, $queue->getEmailBody());
    }

    public function testSetErrorMessageGetErrorMessage(): void
    {
        $queue = new Queue();

        $this->assertNull($queue->getErrorMessage());

        $errorMessage = '发送失败: 无效地址';
        $queue->setErrorMessage($errorMessage);
        $this->assertSame($errorMessage, $queue->getErrorMessage());
    }

    public function testSetDoneIsDone(): void
    {
        $queue = new Queue();

        $this->assertNull($queue->isDone());

        $queue->setDone(true);
        $this->assertTrue($queue->isDone());

        $queue->setDone(false);
        $this->assertFalse($queue->isDone());
    }

    public function testSetValidIsValid(): void
    {
        $queue = new Queue();

        $this->assertFalse($queue->isValid());

        $queue->setValid(true);
        $this->assertTrue($queue->isValid());

        $queue->setValid(false);
        $this->assertFalse($queue->isValid());
    }

    public function testSetCreateTimeGetCreateTime(): void
    {
        $queue = new Queue();

        $now = new \DateTimeImmutable();
        $queue->setCreateTime($now);
        $this->assertSame($now, $queue->getCreateTime());
    }

    public function testSetUpdateTimeGetUpdateTime(): void
    {
        $queue = new Queue();

        $now = new \DateTimeImmutable();
        $queue->setUpdateTime($now);
        $this->assertSame($now, $queue->getUpdateTime());
    }
}
