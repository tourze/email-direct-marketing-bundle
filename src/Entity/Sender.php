<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\SenderRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
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

#[AsPermission(title: '邮件发送器')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: SenderRepository::class)]
#[ORM\Table(name: 'ims_edm_sender', options: ['comment' => '邮件发送器'])]
class Sender implements \Stringable
{
    use TimestampableAware;
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 100, options: ['comment' => '发送器名称'])]
    private ?string $title = null;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 1000, options: ['comment' => 'DSN'])]
    private ?string $dsn = null;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 100, options: ['comment' => '显示名称'])]
    private ?string $senderName = null;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 200, options: ['comment' => '邮箱地址'])]
    private ?string $emailAddress = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreateTimeColumn]
    #[IndexColumn]
    #[ListColumn(sorter: true)]
    #[Filterable]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]#[UpdateTimeColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    public function setDsn(string $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

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
        return $this->title ?? '未命名发送器';
    }
}
