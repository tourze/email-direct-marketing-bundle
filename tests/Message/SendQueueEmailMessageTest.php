<?php

namespace EmailDirectMarketingBundle\Tests\Message;

use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use PHPUnit\Framework\TestCase;

class SendQueueEmailMessageTest extends TestCase
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
