<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use DateTimeImmutable;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private Task $task;

    protected function setUp(): void
    {
        $this->task = new Task();
    }

    public function test_initialState(): void
    {
        $this->assertNull($this->task->getTitle());
        $this->assertSame([], $this->task->getTags());
        $this->assertNull($this->task->getTemplate());
        $this->assertNull($this->task->getStatus());
        $this->assertNull($this->task->getTotalCount());
        $this->assertNull($this->task->getSuccessCount());
        $this->assertNull($this->task->getFailureCount());
        $this->assertNull($this->task->getStartTime());
        $this->assertFalse($this->task->isValid());
        $this->assertCount(0, $this->task->getSenders());
    }

    public function test_setTitle_getTitle(): void
    {
        $title = '测试任务';
        $this->task->setTitle($title);
        $this->assertSame($title, $this->task->getTitle());
    }

    public function test_setTags_getTags(): void
    {
        $tags = ['tag1', 'tag2', 'tag3'];
        $this->task->setTags($tags);
        $this->assertSame($tags, $this->task->getTags());
    }

    public function test_setTemplate_getTemplate(): void
    {
        $template = $this->createMock(Template::class);
        $this->task->setTemplate($template);
        $this->assertSame($template, $this->task->getTemplate());
    }

    public function test_setStatus_getStatus(): void
    {
        $status = TaskStatus::WAITING;
        $this->task->setStatus($status);
        $this->assertSame($status, $this->task->getStatus());
    }

    public function test_setTotalCount_getTotalCount(): void
    {
        $count = 100;
        $this->task->setTotalCount($count);
        $this->assertSame($count, $this->task->getTotalCount());
    }

    public function test_setSuccessCount_getSuccessCount(): void
    {
        $count = 90;
        $this->task->setSuccessCount($count);
        $this->assertSame($count, $this->task->getSuccessCount());
    }

    public function test_setFailureCount_getFailureCount(): void
    {
        $count = 10;
        $this->task->setFailureCount($count);
        $this->assertSame($count, $this->task->getFailureCount());
    }

    public function test_setStartTime_getStartTime(): void
    {
        $time = new DateTimeImmutable();
        $this->task->setStartTime($time);
        $this->assertSame($time, $this->task->getStartTime());
    }

    public function test_addSender_getSenders(): void
    {
        $sender1 = $this->createMock(Sender::class);
        $sender2 = $this->createMock(Sender::class);
        
        $this->task->addSender($sender1);
        $this->task->addSender($sender2);
        
        $this->assertCount(2, $this->task->getSenders());
        $this->assertTrue($this->task->getSenders()->contains($sender1));
        $this->assertTrue($this->task->getSenders()->contains($sender2));
    }

    public function test_addSender_withDuplicate(): void
    {
        $sender = $this->createMock(Sender::class);
        
        $this->task->addSender($sender);
        $this->task->addSender($sender); // 添加相同的sender第二次
        
        $this->assertCount(1, $this->task->getSenders());
    }

    public function test_removeSender(): void
    {
        $sender1 = $this->createMock(Sender::class);
        $sender2 = $this->createMock(Sender::class);
        
        $this->task->addSender($sender1);
        $this->task->addSender($sender2);
        $this->assertCount(2, $this->task->getSenders());
        
        $this->task->removeSender($sender1);
        $this->assertCount(1, $this->task->getSenders());
        $this->assertFalse($this->task->getSenders()->contains($sender1));
        $this->assertTrue($this->task->getSenders()->contains($sender2));
    }

    public function test_setValid_isValid(): void
    {
        $this->assertFalse($this->task->isValid());
        
        $this->task->setValid(true);
        $this->assertTrue($this->task->isValid());
        
        $this->task->setValid(false);
        $this->assertFalse($this->task->isValid());
    }

    public function test_toString(): void
    {
        $title = '测试任务';
        $this->task->setTitle($title);
        $this->assertSame($title, (string) $this->task);
    }
} 