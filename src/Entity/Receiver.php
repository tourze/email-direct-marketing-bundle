<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '客户邮箱')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: ReceiverRepository::class)]
#[ORM\Table(name: 'ims_edm_receiver', options: ['comment' => '客户邮箱'])]
class Receiver
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[Groups(['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
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

    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否退订'])]
    private ?bool $unsubscribed = null;

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
}
