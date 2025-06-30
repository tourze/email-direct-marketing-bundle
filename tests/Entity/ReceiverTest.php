<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Receiver;
use PHPUnit\Framework\TestCase;

class ReceiverTest extends TestCase
{
    private Receiver $receiver;

    protected function setUp(): void
    {
        $this->receiver = new Receiver();
    }

    public function testGetAndSetName(): void
    {
        $name = '张三';
        $this->receiver->setName($name);

        $this->assertSame($name, $this->receiver->getName());
    }

    public function testGetNameReturnsStringValue(): void
    {
        $this->receiver->setName('测试用户');

        $result = $this->receiver->getName();

        $this->assertSame('测试用户', $result);
    }

    public function testGetAndSetEmailAddress(): void
    {
        $emailAddress = 'test@example.com';
        $this->receiver->setEmailAddress($emailAddress);

        $this->assertSame($emailAddress, $this->receiver->getEmailAddress());
    }

    public function testGetEmailAddressReturnsStringValue(): void
    {
        $this->receiver->setEmailAddress('test@example.com');

        $result = $this->receiver->getEmailAddress();

        $this->assertSame('test@example.com', $result);
    }

    public function testGetAndSetTags(): void
    {
        $tags = ['vip', 'premium'];
        $this->receiver->setTags($tags);

        $this->assertSame($tags, $this->receiver->getTags());
    }

    public function testGetTagsReturnsEmptyArrayWhenEmpty(): void
    {
        $this->receiver->setTags([]);

        $result = $this->receiver->getTags();

        $this->assertSame([], $result);
    }

    public function testGetAndSetLastSendTime(): void
    {
        $lastSendTime = new \DateTimeImmutable();
        $this->receiver->setLastSendTime($lastSendTime);

        $this->assertSame($lastSendTime, $this->receiver->getLastSendTime());
    }

    public function testGetAndSetUnsubscribed(): void
    {
        $this->receiver->setUnsubscribed(true);
        $this->assertTrue($this->receiver->isUnsubscribed());

        $this->receiver->setUnsubscribed(false);
        $this->assertFalse($this->receiver->isUnsubscribed());

        $this->receiver->setUnsubscribed(null);
        $this->assertNull($this->receiver->isUnsubscribed());
    }

    public function testToStringWithName(): void
    {
        $name = '张三';
        $this->receiver->setName($name);

        $this->assertSame($name, (string) $this->receiver);
    }

    public function testToStringWithEmailAddressWhenNoName(): void
    {
        $emailAddress = 'test@example.com';
        $this->receiver->setEmailAddress($emailAddress);

        $result = (string) $this->receiver;

        $this->assertSame($emailAddress, $result);
    }

    public function testToStringDefault(): void
    {
        $result = (string) $this->receiver;

        $this->assertSame('未命名接收者', $result);
    }

    public function testImplementsStringable(): void
    {
        $this->assertInstanceOf('Stringable', $this->receiver);
    }
}
