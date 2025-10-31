<?php

namespace EmailDirectMarketingBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EmailDirectMarketingBundle\Entity\Receiver;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Receiver>
 */
#[Autoconfigure(public: true)]
#[AdminCrud(routePath: '/email-marketing/receiver', routeName: 'email_marketing_receiver')]
final class ReceiverCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ?AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Receiver::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('收件人')
            ->setEntityLabelInPlural('收件人')
            ->setPageTitle('index', '收件人列表')
            ->setPageTitle('new', '添加收件人')
            ->setPageTitle('edit', fn (Receiver $receiver) => sprintf('编辑收件人: %s', $receiver->getName()))
            ->setPageTitle('detail', fn (Receiver $receiver) => sprintf('收件人详情: %s', $receiver->getName()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'name', 'emailAddress', 'tags'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield FormField::addPanel('基本信息')
            ->setIcon('fa fa-user')
        ;

        yield TextField::new('name', '称呼')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('收件人的称呼或姓名')
        ;

        yield EmailField::new('emailAddress', '邮箱地址')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('收件人的邮箱地址')
        ;

        yield ArrayField::new('tags', '标签')
            ->setColumns(12)
            ->setHelp('添加标签以便于分组管理，例如: ["vip", "active"]')
        ;

        yield FormField::addPanel('状态信息')
            ->setIcon('fa fa-info-circle')
        ;

        yield DateTimeField::new('lastSendTime', '上次发送时间')
            ->hideOnForm()
        ;

        yield BooleanField::new('unsubscribed', '已退订')
            ->setColumns(12)
            ->setHelp('勾选表示该收件人已退订邮件')
        ;

        yield FormField::addPanel('系统信息')
            ->setIcon('fa fa-cog')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $unsubscribeAction = Action::new('unsubscribe', '退订')
            ->setIcon('fa fa-ban')
            ->setCssClass('btn btn-warning')
            ->displayIf(function (Receiver $receiver) {
                return false === $receiver->isUnsubscribed();
            })
            ->linkToCrudAction('unsubscribeReceiver')
        ;

        $resubscribeAction = Action::new('resubscribe', '重新订阅')
            ->setIcon('fa fa-check')
            ->setCssClass('btn btn-success')
            ->displayIf(function (Receiver $receiver) {
                return $receiver->isUnsubscribed();
            })
            ->linkToCrudAction('resubscribeReceiver')
        ;

        $viewHistoryAction = Action::new('viewHistory', '查看历史')
            ->setIcon('fa fa-history')
            ->setCssClass('btn btn-info')
            ->linkToCrudAction('viewSendHistory')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $unsubscribeAction)
            ->add(Crud::PAGE_DETAIL, $resubscribeAction)
            ->add(Crud::PAGE_DETAIL, $viewHistoryAction)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('emailAddress')
            ->add('unsubscribed')
            ->add('lastSendTime')
            ->add('createTime')
        ;
    }

    /**
     * 退订收件人
     */
    #[AdminAction(routePath: '{entityId}/unsubscribe', routeName: 'unsubscribe_receiver')]
    public function unsubscribeReceiver(AdminContext $context): Response
    {
        $receiver = $context->getEntity()->getInstance();
        assert($receiver instanceof Receiver);

        if (true === $receiver->isUnsubscribed()) {
            $this->addFlash('warning', sprintf('收件人 %s 已经退订', $receiver->getName()));

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->adminUrlGenerator?->setController(self::class)->generateUrl() ?? '/');
        }

        $receiver->setUnsubscribed(true);
        $this->entityManager->persist($receiver);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('收件人 %s 已成功退订', $receiver->getName()));

        return $this->redirect($this->adminUrlGenerator
            ?->setAction(Action::DETAIL)
            ->setEntityId($receiver->getId())
            ->generateUrl() ?? '/');
    }

    /**
     * 重新订阅收件人
     */
    #[AdminAction(routePath: '{entityId}/resubscribe', routeName: 'resubscribe_receiver')]
    public function resubscribeReceiver(AdminContext $context): Response
    {
        $receiver = $context->getEntity()->getInstance();
        assert($receiver instanceof Receiver);

        if (false === $receiver->isUnsubscribed()) {
            $this->addFlash('warning', sprintf('收件人 %s 未退订', $receiver->getName()));

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->adminUrlGenerator?->setController(self::class)->generateUrl() ?? '/');
        }

        $receiver->setUnsubscribed(false);
        $this->entityManager->persist($receiver);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('收件人 %s 已重新订阅', $receiver->getName()));

        return $this->redirect($this->adminUrlGenerator
            ?->setAction(Action::DETAIL)
            ->setEntityId($receiver->getId())
            ->generateUrl() ?? '/');
    }

    /**
     * 查看发送历史
     */
    #[AdminAction(routePath: '{entityId}/history', routeName: 'view_send_history')]
    public function viewSendHistory(AdminContext $context): Response
    {
        $receiver = $context->getEntity()->getInstance();
        assert($receiver instanceof Receiver);

        return $this->redirect($this->adminUrlGenerator
            ?->unsetAll()
            ->setController(QueueCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[receiver][comparison]', '=')
            ->set('filters[receiver][value]', $receiver->getId())
            ->generateUrl() ?? '/');
    }
}
