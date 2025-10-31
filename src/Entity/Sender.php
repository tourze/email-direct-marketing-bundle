<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\SenderRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;

#[ORM\Entity(repositoryClass: SenderRepository::class)]
#[ORM\Table(name: 'ims_edm_sender', options: ['comment' => '邮件发送器'])]
class Sender implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '发送器名称'])]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    #[TrackColumn]
    #[ORM\Column(length: 1000, options: ['comment' => 'DSN'])]
    private ?string $dsn = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '显示名称'])]
    private ?string $senderName = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 200)]
    #[TrackColumn]
    #[ORM\Column(length: 200, options: ['comment' => '邮箱地址'])]
    private ?string $emailAddress = null;

    #[Assert\Type(type: 'bool')]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }

    public function getEmailAddress(): string
    {
        return strval($this->emailAddress);
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function __toString(): string
    {
        return $this->title ?? '未命名发送器';
    }
}
