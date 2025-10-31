<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'ims_edm_task', options: ['comment' => '营销任务'])]
class Task implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[TrackColumn]
    #[ORM\Column(length: 120, options: ['comment' => '任务名'])]
    private ?string $title = null;

    /**
     * @var array<int, string>
     */
    #[Assert\Type(type: 'array')]
    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '发送标签'])]
    private array $tags = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Template $template = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [TaskStatus::class, 'cases'])]
    #[TrackColumn]
    #[ORM\Column(length: 30, enumType: TaskStatus::class, options: ['comment' => '状态', 'default' => 'waiting'])]
    private ?TaskStatus $status = null;

    #[Assert\PositiveOrZero]
    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '总发送数量'])]
    private ?int $totalCount = null;

    #[Assert\PositiveOrZero]
    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '成功数量'])]
    private ?int $successCount = null;

    #[Assert\PositiveOrZero]
    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '失败数量'])]
    private ?int $failureCount = null;

    #[Assert\NotNull]
    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    /**
     * @var Collection<int, Sender>
     */
    #[ORM\ManyToMany(targetEntity: Sender::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'ims_edm_task_sender')]
    private Collection $senders;

    #[Assert\Type(type: 'bool')]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->senders = new ArrayCollection();
    }

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

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): void
    {
        $this->template = $template;
    }

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function setTotalCount(?int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    public function getSuccessCount(): ?int
    {
        return $this->successCount;
    }

    public function setSuccessCount(?int $successCount): void
    {
        $this->successCount = $successCount;
    }

    public function getFailureCount(): ?int
    {
        return $this->failureCount;
    }

    public function setFailureCount(?int $failureCount): void
    {
        $this->failureCount = $failureCount;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return Collection<int, Sender>
     */
    public function getSenders(): Collection
    {
        return $this->senders;
    }

    public function addSender(Sender $sender): self
    {
        if (!$this->senders->contains($sender)) {
            $this->senders->add($sender);
        }

        return $this;
    }

    public function removeSender(Sender $sender): self
    {
        $this->senders->removeElement($sender);

        return $this;
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
        return $this->title ?? '未命名任务';
    }
}
