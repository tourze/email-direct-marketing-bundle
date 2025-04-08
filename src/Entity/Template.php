<?php

namespace EmailDirectMarketingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EmailDirectMarketingBundle\Repository\TemplateRepository;
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

#[AsPermission(title: '邮件模板')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'ims_edm_template', options: ['comment' => '邮件模板'])]
class Template
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[Groups(['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 100, options: ['comment' => '模板名'])]
    private ?string $name = null;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 120, options: ['comment' => '邮件主题'])]
    private ?string $subject = null;

    #[TrackColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '邮件内容'])]
    private ?string $htmlBody = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubject(): string
    {
        return strval($this->subject);
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    public function setHtmlBody(string $htmlBody): self
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    public function getHTML(): string
    {
        return strval($this->getHtmlBody());
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
