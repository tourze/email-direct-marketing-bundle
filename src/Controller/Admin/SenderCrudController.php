<?php

namespace EmailDirectMarketingBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EmailDirectMarketingBundle\Entity\Sender;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class SenderCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public static function getEntityFqcn(): string
    {
        return Sender::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('邮件发送器')
            ->setEntityLabelInPlural('邮件发送器')
            ->setPageTitle('index', '邮件发送器列表')
            ->setPageTitle('new', '创建邮件发送器')
            ->setPageTitle('edit', fn(Sender $sender) => sprintf('编辑发送器: %s', $sender->getTitle()))
            ->setPageTitle('detail', fn(Sender $sender) => sprintf('发送器详情: %s', $sender->getTitle()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'emailAddress', 'senderName']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield FormField::addPanel('基本信息')
            ->setIcon('fa fa-envelope');

        yield TextField::new('title', '发送器名称')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('输入发送器名称，便于管理和识别');

        yield TextField::new('dsn', 'DSN')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('例如: smtp://username:password@smtp.example.com:587');

        yield TextField::new('senderName', '显示名称')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('发送邮件时显示的名称，例如: 公司名称');

        yield EmailField::new('emailAddress', '邮箱地址')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('发送邮件的邮箱地址');

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
        $testAction = Action::new('testSender', '测试')
            ->setIcon('fa fa-check-circle')
            ->setCssClass('btn btn-success')
            ->linkToCrudAction('testSender');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $testAction)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('emailAddress')
            ->add('createTime')
            ->add('valid');
    }

    /**
     * 测试发送器配置
     */
    #[AdminAction(routePath: '{entityId}/test', routeName: 'test_sender')]
    public function testSender(AdminContext $context): Response
    {
        /** @var Sender $sender */
        $sender = $context->getEntity()->getInstance();

        // 创建一封测试邮件
        $email = (new Email())
            ->from($sender->getEmailAddress())
            ->to($sender->getEmailAddress())
            ->subject('测试邮件 - ' . date('Y-m-d H:i:s'))
            ->html('<p>这是一封测试邮件，用于验证邮件发送器配置。</p><p>发送时间: ' . date('Y-m-d H:i:s') . '</p>');

        try {
            // 创建发送器
            $transport = Transport::fromDsn($sender->getDsn());
            $mailer = new Mailer($transport);

            // 发送邮件
            $mailer->send($email);

            // 更新最后测试时间
            $sender->setUpdateTime(new \DateTimeImmutable());
            $this->entityManager->persist($sender);
            $this->entityManager->flush();

            $this->addFlash('success', '测试邮件发送成功，请检查收件箱。');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('danger', '测试邮件发送失败: ' . $e->getMessage());
        }

        return $this->redirect($context->getReferrer());
    }
}
