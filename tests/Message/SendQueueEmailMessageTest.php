<?php

namespace EmailDirectMarketingBundle\Tests\Message;

use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(SendQueueEmailMessage::class)]
final class SendQueueEmailMessageTest extends AbstractExceptionTestCase
{
    public function testQueueIdGetterAndSetter(): void
    {
        $message = new SendQueueEmailMessage();
        $queueId = 123;

        $message->setQueueId($queueId);

        $this->assertSame($queueId, $message->getQueueId());
    }

    public function testImplementsAsyncMessageInterface(): void
    {
        $message = new SendQueueEmailMessage();

        $this->assertInstanceOf('Tourze\AsyncContracts\AsyncMessageInterface', $message);
    }
}
