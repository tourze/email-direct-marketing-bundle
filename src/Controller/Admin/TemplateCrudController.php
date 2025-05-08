<?php

namespace EmailDirectMarketingBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EmailDirectMarketingBundle\Entity\Template;

class TemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Template::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('邮件模板')
            ->setEntityLabelInPlural('邮件模板')
            ->setPageTitle('index', '邮件模板列表')
            ->setPageTitle('new', '创建邮件模板')
            ->setPageTitle('edit', fn(Template $template) => sprintf('编辑模板: %s', $template->getName()))
            ->setPageTitle('detail', fn(Template $template) => sprintf('模板详情: %s', $template->getName()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'name', 'subject', 'htmlBody']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield FormField::addPanel('模板信息')
            ->setIcon('fa fa-paper-plane');

        yield TextField::new('name', '模板名称')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('输入模板名称，便于管理和查找');

        yield TextField::new('subject', '邮件主题')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('邮件主题会显示在收件人的邮件列表中，支持变量格式：${receiver.getName()}');

        yield TextareaField::new('htmlBody', '邮件内容')
            ->setRequired(true)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('支持HTML格式，变量使用格式：${receiver.getName()}, ${task.getTitle()}, ${now.format("Y-m-d")}等')
            ->setFormTypeOption('attr', [
                'rows' => 15,
                'class' => 'html-editor',
            ]);

        yield FormField::addPanel('系统信息')
            ->setIcon('fa fa-cog');

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm();

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm();

        yield BooleanField::new('valid', '有效')
            ->setColumns(12);
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewAction = Action::new('previewTemplate', '预览')
            ->setIcon('fa fa-eye')
            ->setCssClass('btn btn-info')
            ->linkToUrl(function (Template $template) {
                return sprintf('/admin?preview_template=%d', $template->getId());
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $previewAction)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('subject')
            ->add('createTime')
            ->add('valid');
    }
}
