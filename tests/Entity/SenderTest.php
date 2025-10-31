<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Sender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Sender::class)]
final class SenderTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Sender();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', 'Test Sender'];
        yield 'dsn' => ['dsn', 'smtp://user:pass@host:587'];
        yield 'senderName' => ['senderName', 'Test Sender Name'];
        yield 'emailAddress' => ['emailAddress', 'test@example.com'];
        yield 'valid' => ['valid', true];
    }

    public function testGetAndSetTitle(): void
    {
        $sender = new Sender();

        $title = '测试发送器';
        $sender->setTitle($title);

        $this->assertSame($title, $sender->getTitle());
    }

    public function testGetAndSetDsn(): void
    {
        $sender = new Sender();

        $dsn = 'smtp://user:pass@host:587';
        $sender->setDsn($dsn);

        $this->assertSame($dsn, $sender->getDsn());
    }

    public function testGetAndSetSenderName(): void
    {
        $sender = new Sender();

        $senderName = '测试发送者';
        $sender->setSenderName($senderName);

        $this->assertSame($senderName, $sender->getSenderName());
    }

    public function testGetAndSetEmailAddress(): void
    {
        $sender = new Sender();

        $emailAddress = 'test@example.com';
        $sender->setEmailAddress($emailAddress);

        $this->assertSame($emailAddress, $sender->getEmailAddress());
    }

    public function testGetEmailAddressReturnsStringValue(): void
    {
        $sender = new Sender();

        $sender->setEmailAddress('test@example.com');

        $result = $sender->getEmailAddress();

        $this->assertSame('test@example.com', $result);
    }

    public function testGetAndSetValid(): void
    {
        $sender = new Sender();

        $sender->setValid(true);
        $this->assertTrue($sender->isValid());

        $sender->setValid(false);
        $this->assertFalse($sender->isValid());

        $sender->setValid(null);
        $this->assertNull($sender->isValid());
    }

    public function testToStringWithTitle(): void
    {
        $sender = new Sender();

        $title = '测试发送器';
        $sender->setTitle($title);

        $this->assertSame($title, (string) $sender);
    }

    public function testToStringWithoutTitle(): void
    {
        $sender = new Sender();

        $result = (string) $sender;

        $this->assertSame('未命名发送器', $result);
    }

    public function testImplementsStringable(): void
    {
        $sender = new Sender();

        $this->assertInstanceOf('Stringable', $sender);
    }
}
