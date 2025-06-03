<?php

namespace EmailDirectMarketingBundle\Message;

use Tourze\AsyncContracts\AsyncMessageInterface;

class SendQueueEmailMessage implements AsyncMessageInterface
{
    /**
     * @var int 发送队列ID
     */
    private int $queueId;

    public function getQueueId(): int
    {
        return $this->queueId;
    }

    public function setQueueId(int $queueId): void
    {
        $this->queueId = $queueId;
    }
}
