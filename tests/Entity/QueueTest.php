<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    private Queue $queue;

    protected function setUp(): void
    {
        $this->queue = new Queue();
    }

    public function test_initialState(): void
    {
        $this->assertSame(0, $this->queue->getId());
        $this->assertNull($this->queue->getTask());
        $this->assertNull($this->queue->getSender());
        $this->assertNull($this->queue->getReceiver());
        $this->assertNull($this->queue->getEmailSubject());
        $this->assertNull($this->queue->getEmailBody());
        $this->assertNull($this->queue->getErrorMessage());
        $this->assertNull($this->queue->isDone());
        $this->assertFalse($this->queue->isValid());
        $this->assertNull($this->queue->getCreateTime());
        $this->assertNull($this->queue->getUpdateTime());
    }

    public function test_setTask_getTask(): void
    {
        $task = $this->createMock(Task::class);
        $this->queue->setTask($task);
        $this->assertSame($task, $this->queue->getTask());
    }

    public function test_setSender_getSender(): void
    {
        $sender = $this->createMock(Sender::class);
        $this->queue->setSender($sender);
        $this->assertSame($sender, $this->queue->getSender());
    }

    public function test_setReceiver_getReceiver(): void
    {
        $receiver = $this->createMock(Receiver::class);
        $this->queue->setReceiver($receiver);
        $this->assertSame($receiver, $this->queue->getReceiver());
    }

    public function test_setEmailSubject_getEmailSubject(): void
    {
        $subject = '测试邮件标题';
        $this->queue->setEmailSubject($subject);
        $this->assertSame($subject, $this->queue->getEmailSubject());
    }

    public function test_setEmailBody_getEmailBody(): void
    {
        $body = '<p>测试邮件内容</p>';
        $this->queue->setEmailBody($body);
        $this->assertSame($body, $this->queue->getEmailBody());
    }

    public function test_setErrorMessage_getErrorMessage(): void
    {
        $this->assertNull($this->queue->getErrorMessage());
        
        $errorMessage = '发送失败: 无效地址';
        $this->queue->setErrorMessage($errorMessage);
        $this->assertSame($errorMessage, $this->queue->getErrorMessage());
    }

    public function test_setDone_isDone(): void
    {
        $this->assertNull($this->queue->isDone());
        
        $this->queue->setDone(true);
        $this->assertTrue($this->queue->isDone());
        
        $this->queue->setDone(false);
        $this->assertFalse($this->queue->isDone());
    }

    public function test_setValid_isValid(): void
    {
        $this->assertFalse($this->queue->isValid());
        
        $this->queue->setValid(true);
        $this->assertTrue($this->queue->isValid());
        
        $this->queue->setValid(false);
        $this->assertFalse($this->queue->isValid());
    }

    public function test_setCreateTime_getCreateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->queue->setCreateTime($now);
        $this->assertSame($now, $this->queue->getCreateTime());
    }

    public function test_setUpdateTime_getUpdateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->queue->setUpdateTime($now);
        $this->assertSame($now, $this->queue->getUpdateTime());
    }
} 