<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\TaskRepository;
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
    private ?int $id = 0;

    #[TrackColumn]
    #[ORM\Column(length: 120, options: ['comment' => '任务名'])]
    private ?string $title = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '发送标签'])]
    private array $tags = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Template $template = null;

    #[TrackColumn]
    #[ORM\Column(length: 30, enumType: TaskStatus::class, options: ['comment' => '状态', 'default' => 'waiting'])]
    private ?TaskStatus $status = null;

    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '总发送数量'])]
    private ?int $totalCount = null;

    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '成功数量'])]
    private ?int $successCount = null;

    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '失败数量'])]
    private ?int $failureCount = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\ManyToMany(targetEntity: Sender::class, fetch: 'EXTRA_LAZY')]
    private Collection $senders;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->senders = new ArrayCollection();
    }

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

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function setTotalCount(?int $totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    public function getSuccessCount(): ?int
    {
        return $this->successCount;
    }

    public function setSuccessCount(?int $successCount): self
    {
        $this->successCount = $successCount;

        return $this;
    }

    public function getFailureCount(): ?int
    {
        return $this->failureCount;
    }

    public function setFailureCount(?int $failureCount): self
    {
        $this->failureCount = $failureCount;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
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

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }public function __toString(): string
    {
        return $this->title ?? '未命名任务';
    }
}
