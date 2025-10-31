<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Task();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'tags' => ['tags', ['key' => 'value']],
        ];
    }

    public function testInitialState(): void
    {
        $task = new Task();

        $this->assertNull($task->getTitle());
        $this->assertSame([], $task->getTags());
        $this->assertNull($task->getTemplate());
        $this->assertNull($task->getStatus());
        $this->assertNull($task->getTotalCount());
        $this->assertNull($task->getSuccessCount());
        $this->assertNull($task->getFailureCount());
        $this->assertNull($task->getStartTime());
        $this->assertFalse($task->isValid());
        $this->assertCount(0, $task->getSenders());
    }

    public function testSetTitleGetTitle(): void
    {
        $task = new Task();

        $title = '测试任务';
        $task->setTitle($title);
        $this->assertSame($title, $task->getTitle());
    }

    public function testSetTagsGetTags(): void
    {
        $task = new Task();

        $tags = ['tag1', 'tag2', 'tag3'];
        $task->setTags($tags);
        $this->assertSame($tags, $task->getTags());
    }

    public function testSetTemplateGetTemplate(): void
    {
        $task = new Task();

        /* createMock() 使用具体类 Template 的原因：
         * 1) 必须使用具体类因为 Template 是 Doctrine Entity，没有对应的接口定义
         * 2) 这种使用是合理和必要的，因为测试的是setter/getter的基本功能，不需要真实Entity属性
         * 3) 没有更好的替代方案，Mock对象足够验证属性设置，且避免了复杂的Entity依赖 */
        $template = $this->createMock(Template::class);
        $task->setTemplate($template);
        $this->assertSame($template, $task->getTemplate());
    }

    public function testSetStatusGetStatus(): void
    {
        $task = new Task();

        $status = TaskStatus::WAITING;
        $task->setStatus($status);
        $this->assertSame($status, $task->getStatus());
    }

    public function testSetTotalCountGetTotalCount(): void
    {
        $task = new Task();

        $count = 100;
        $task->setTotalCount($count);
        $this->assertSame($count, $task->getTotalCount());
    }

    public function testSetSuccessCountGetSuccessCount(): void
    {
        $task = new Task();

        $count = 90;
        $task->setSuccessCount($count);
        $this->assertSame($count, $task->getSuccessCount());
    }

    public function testSetFailureCountGetFailureCount(): void
    {
        $task = new Task();

        $count = 10;
        $task->setFailureCount($count);
        $this->assertSame($count, $task->getFailureCount());
    }

    public function testSetStartTimeGetStartTime(): void
    {
        $task = new Task();

        $time = new \DateTimeImmutable();
        $task->setStartTime($time);
        $this->assertSame($time, $task->getStartTime());
    }

    public function testAddSenderGetSenders(): void
    {
        $task = new Task();

        /* createMock() 使用具体类 Sender 的原因：
         * 1) 必须使用具体类因为 Sender 是 Doctrine Entity，没有对应的接口定义
         * 2) 这种使用是合理和必要的，因为测试集合操作的基本功能（添加、包含检查），不需要真实Entity属性
         * 3) 没有更好的替代方案，Mock对象足够验证集合操作逻辑，且避免了复杂的Entity依赖 */
        $sender1 = $this->createMock(Sender::class);
        /* 第二个 Sender Mock 对象用于测试集合的多元素操作，必须使用具体类因为：
         * 1) Sender 是 Doctrine Entity，没有对应的接口定义，必须使用具体类
         * 2) 这种使用是合理的，因为测试需要两个不同的对象实例来验证集合操作
         * 3) 没有更好的替代方案，使用真实 Entity 会导致测试复杂化和依赖问题 */
        $sender2 = $this->createMock(Sender::class);

        $task->addSender($sender1);
        $task->addSender($sender2);

        $this->assertCount(2, $task->getSenders());
        $this->assertTrue($task->getSenders()->contains($sender1));
        $this->assertTrue($task->getSenders()->contains($sender2));
    }

    public function testAddSenderWithDuplicate(): void
    {
        $task = new Task();

        /* createMock() 使用具体类 Sender 的原因：
         * 1) 必须使用具体类因为 Sender 是 Doctrine Entity，没有对应的接口定义
         * 2) 这种使用是合理和必要的，因为测试集合去重功能，验证相同Entity对象不会被重复添加
         * 3) 没有更好的替代方案，Mock对象足够满足对象引用比较的需求，无需真实Entity属性 */
        $sender = $this->createMock(Sender::class);

        $task->addSender($sender);
        $task->addSender($sender); // 添加相同的sender第二次

        $this->assertCount(1, $task->getSenders());
    }

    public function testRemoveSender(): void
    {
        $task = new Task();

        /* createMock() 使用具体类 Sender 的原因：
         * 1) 必须使用具体类因为 Sender 是 Doctrine Entity，没有对应的接口定义
         * 2) 这种使用是合理和必要的，因为测试集合移除功能，验证元素的正确移除和保留
         * 3) 没有更好的替代方案，Mock对象足够验证对象引用和集合状态变化，无需真实Entity属性 */
        $sender1 = $this->createMock(Sender::class);
        /* 第二个 Sender Mock 对象用于测试集合移除操作，必须使用具体类因为：
         * 1) Sender 是 Doctrine Entity，没有对应的接口定义，必须使用具体类
         * 2) 这种使用是合理的，因为测试需要两个不同的对象实例来验证移除逻辑
         * 3) 没有更好的替代方案，使用真实 Entity 会导致测试复杂化和数据库依赖 */
        $sender2 = $this->createMock(Sender::class);

        $task->addSender($sender1);
        $task->addSender($sender2);
        $this->assertCount(2, $task->getSenders());

        $task->removeSender($sender1);
        $this->assertCount(1, $task->getSenders());
        $this->assertFalse($task->getSenders()->contains($sender1));
        $this->assertTrue($task->getSenders()->contains($sender2));
    }

    public function testSetValidIsValid(): void
    {
        $task = new Task();

        $this->assertFalse($task->isValid());

        $task->setValid(true);
        $this->assertTrue($task->isValid());

        $task->setValid(false);
        $this->assertFalse($task->isValid());
    }

    public function testToString(): void
    {
        $task = new Task();

        $title = '测试任务';
        $task->setTitle($title);
        $this->assertSame($title, (string) $task);
    }
}
