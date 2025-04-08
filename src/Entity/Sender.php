<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\SenderRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '邮件发送器')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: SenderRepository::class)]
#[ORM\Table(name: 'ims_edm_sender', options: ['comment' => '邮件发送器'])]
class Sender
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[Groups(['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '发送器名称'])]
    private ?string $title = null;

    #[TrackColumn]
    #[ORM\Column(length: 1000, options: ['comment' => 'DSN'])]
    private ?string $dsn = null;

    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '显示名称'])]
    private ?string $senderName = null;

    #[TrackColumn]
    #[ORM\Column(length: 200, options: ['comment' => '邮箱地址'])]
    private ?string $emailAddress = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[Groups(['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    public function getId(): ?int
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
    }
}
