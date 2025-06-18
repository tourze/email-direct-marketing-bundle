<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;

#[ORM\Entity(repositoryClass: QueueRepository::class)]
#[ORM\Table(name: 'ims_edm_queue', options: ['comment' => 'EDM邮件发送队列'])]
class Queue implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Receiver $receiver = null;

    #[TrackColumn]
    #[ORM\Column(length: 200, options: ['comment' => '邮件主题'])]
    private ?string $emailSubject = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '邮件内容'])]
    private ?string $emailBody = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sender $sender = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发送时间'])]
    private ?\DateTimeInterface $sendTime = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '是否已完成'])]
    private ?bool $done = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    private ?string $errorMessage = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getReceiver(): ?Receiver
    {
        return $this->receiver;
    }

    public function setReceiver(?Receiver $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): self
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getEmailBody(): ?string
    {
        return $this->emailBody;
    }

    public function setEmailBody(string $emailBody): self
    {
        $this->emailBody = $emailBody;

        return $this;
    }

    public function getSender(): ?Sender
    {
        return $this->sender;
    }

    public function setSender(?Sender $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSendTime(): ?\DateTimeInterface
    {
        return $this->sendTime;
    }

    public function setSendTime(?\DateTimeInterface $sendTime): self
    {
        $this->sendTime = $sendTime;

        return $this;
    }

    public function isDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(?bool $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }public function __toString(): string
    {
        return '邮件队列 #' . $this->id;
    }
}
