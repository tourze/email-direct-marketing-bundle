<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Sender;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    private Sender $sender;

    protected function setUp(): void
    {
        $this->sender = new Sender();
    }

    public function testGetAndSetTitle(): void
    {
        $title = '测试发送器';
        $this->sender->setTitle($title);

        $this->assertSame($title, $this->sender->getTitle());
    }

    public function testGetAndSetDsn(): void
    {
        $dsn = 'smtp://user:pass@host:587';
        $this->sender->setDsn($dsn);

        $this->assertSame($dsn, $this->sender->getDsn());
    }

    public function testGetAndSetSenderName(): void
    {
        $senderName = '测试发送者';
        $this->sender->setSenderName($senderName);

        $this->assertSame($senderName, $this->sender->getSenderName());
    }

    public function testGetAndSetEmailAddress(): void
    {
        $emailAddress = 'test@example.com';
        $this->sender->setEmailAddress($emailAddress);

        $this->assertSame($emailAddress, $this->sender->getEmailAddress());
    }

    public function testGetEmailAddressReturnsStringValue(): void
    {
        $this->sender->setEmailAddress('test@example.com');

        $result = $this->sender->getEmailAddress();

        $this->assertSame('test@example.com', $result);
    }

    public function testGetAndSetValid(): void
    {
        $this->sender->setValid(true);
        $this->assertTrue($this->sender->isValid());

        $this->sender->setValid(false);
        $this->assertFalse($this->sender->isValid());

        $this->sender->setValid(null);
        $this->assertNull($this->sender->isValid());
    }

    public function testToStringWithTitle(): void
    {
        $title = '测试发送器';
        $this->sender->setTitle($title);

        $this->assertSame($title, (string) $this->sender);
    }

    public function testToStringWithoutTitle(): void
    {
        $result = (string) $this->sender;

        $this->assertSame('未命名发送器', $result);
    }

    public function testImplementsStringable(): void
    {
        $this->assertInstanceOf('Stringable', $this->sender);
    }
}
