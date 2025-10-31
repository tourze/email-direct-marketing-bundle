<?php

namespace EmailDirectMarketingBundle\Tests\Enum;

use EmailDirectMarketingBundle\Enum\TaskStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(TaskStatus::class)]
final class TaskStatusTest extends AbstractEnumTestCase
{
    public function testEnumCasesExist(): void
    {
        $this->assertSame('waiting', TaskStatus::WAITING->value);
        $this->assertSame('sending', TaskStatus::SENDING->value);
        $this->assertSame('finished', TaskStatus::FINISHED->value);
    }

    public function testGetLabelReturnsCorrectLabel(): void
    {
        $this->assertSame('等待发送', TaskStatus::WAITING->getLabel());
        $this->assertSame('发送中', TaskStatus::SENDING->getLabel());
        $this->assertSame('已完成', TaskStatus::FINISHED->getLabel());
    }

    public function testToSelectItemReturnsCorrectItems(): void
    {
        $item = TaskStatus::WAITING->toSelectItem();
        $this->assertArrayHasKey('label', $item);
        $this->assertArrayHasKey('value', $item);
        $this->assertArrayHasKey('text', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertSame('等待发送', $item['label']);
        $this->assertSame('waiting', $item['value']);
    }

    public function testToArrayReturnsCorrectArray(): void
    {
        $array = TaskStatus::SENDING->toArray();
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertSame('发送中', $array['label']);
        $this->assertSame('sending', $array['value']);
    }

    public function testGenOptionsReturnsAllOptions(): void
    {
        $options = TaskStatus::genOptions();
        $this->assertCount(3, $options);

        // 检查第一个选项
        $this->assertArrayHasKey('label', $options[0]);
        $this->assertArrayHasKey('value', $options[0]);

        // 验证所有值都在选项中
        $values = array_column($options, 'value');
        $this->assertContains('waiting', $values);
        $this->assertContains('sending', $values);
        $this->assertContains('finished', $values);
    }
}
