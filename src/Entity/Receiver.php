<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '客户邮箱')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: ReceiverRepository::class)]
#[ORM\Table(name: 'ims_edm_receiver', options: ['comment' => '客户邮箱'])]
class Receiver implements \Stringable
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '称呼'])]
    private ?string $name = null;

    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 200, options: ['comment' => '邮箱地址'])]
    private ?string $emailAddress = null;

    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '标签'])]
    private array $tags = [];

    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '上次发送时间'])]
    private ?\DateTimeInterface $lastSendTime = null;

    #[BoolColumn]
    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否退订'])]
    private ?bool $unsubscribed = null;

    #[CreateTimeColumn]
    #[IndexColumn]
    #[ListColumn(sorter: true)]
    #[Filterable]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return strval($this->name);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailAddress(): string
    {
        return strval($this->emailAddress);
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags ?: [];
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getLastSendTime(): ?\DateTimeInterface
    {
        return $this->lastSendTime;
    }

    public function setLastSendTime(?\DateTimeInterface $lastSendTime): self
    {
        $this->lastSendTime = $lastSendTime;

        return $this;
    }

    public function isUnsubscribed(): ?bool
    {
        return $this->unsubscribed;
    }

    public function setUnsubscribed(?bool $unsubscribed): self
    {
        $this->unsubscribed = $unsubscribed;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setCreateTime(?\DateTimeInterface $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? $this->emailAddress ?? '未命名接收者';
    }
}
