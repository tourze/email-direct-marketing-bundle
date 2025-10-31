<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Receiver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Receiver::class)]
final class ReceiverTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Receiver();
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

    public function testGetAndSetName(): void
    {
        $receiver = new Receiver();

        $name = '张三';
        $receiver->setName($name);

        $this->assertSame($name, $receiver->getName());
    }

    public function testGetNameReturnsStringValue(): void
    {
        $receiver = new Receiver();

        $receiver->setName('测试用户');

        $result = $receiver->getName();

        $this->assertSame('测试用户', $result);
    }

    public function testGetAndSetEmailAddress(): void
    {
        $receiver = new Receiver();

        $emailAddress = 'test@example.com';
        $receiver->setEmailAddress($emailAddress);

        $this->assertSame($emailAddress, $receiver->getEmailAddress());
    }

    public function testGetEmailAddressReturnsStringValue(): void
    {
        $receiver = new Receiver();

        $receiver->setEmailAddress('test@example.com');

        $result = $receiver->getEmailAddress();

        $this->assertSame('test@example.com', $result);
    }

    public function testGetAndSetTags(): void
    {
        $receiver = new Receiver();

        $tags = ['vip', 'premium'];
        $receiver->setTags($tags);

        $this->assertSame($tags, $receiver->getTags());
    }

    public function testGetTagsReturnsEmptyArrayWhenEmpty(): void
    {
        $receiver = new Receiver();

        $receiver->setTags([]);

        $result = $receiver->getTags();

        $this->assertSame([], $result);
    }

    public function testGetAndSetLastSendTime(): void
    {
        $receiver = new Receiver();

        $lastSendTime = new \DateTimeImmutable();
        $receiver->setLastSendTime($lastSendTime);

        $this->assertSame($lastSendTime, $receiver->getLastSendTime());
    }

    public function testGetAndSetUnsubscribed(): void
    {
        $receiver = new Receiver();

        $receiver->setUnsubscribed(true);
        $this->assertTrue($receiver->isUnsubscribed());

        $receiver->setUnsubscribed(false);
        $this->assertFalse($receiver->isUnsubscribed());

        $receiver->setUnsubscribed(null);
        $this->assertNull($receiver->isUnsubscribed());
    }

    public function testToStringWithName(): void
    {
        $receiver = new Receiver();

        $name = '张三';
        $receiver->setName($name);

        $this->assertSame($name, (string) $receiver);
    }

    public function testToStringWithEmailAddressWhenNoName(): void
    {
        $receiver = new Receiver();

        $emailAddress = 'test@example.com';
        $receiver->setEmailAddress($emailAddress);

        $result = (string) $receiver;

        $this->assertSame($emailAddress, $result);
    }

    public function testToStringDefault(): void
    {
        $receiver = new Receiver();

        $result = (string) $receiver;

        $this->assertSame('未命名接收者', $result);
    }

    public function testImplementsStringable(): void
    {
        $receiver = new Receiver();

        $this->assertInstanceOf('Stringable', $receiver);
    }
}
