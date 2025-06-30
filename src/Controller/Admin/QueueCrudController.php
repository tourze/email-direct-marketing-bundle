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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class QueueCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Queue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('邮件队列')
            ->setEntityLabelInPlural('邮件队列')
            ->setPageTitle('index', '邮件队列列表')
            ->setPageTitle('new', '创建邮件队列')
            ->setPageTitle('edit', fn(Queue $queue) => sprintf('编辑队列 #%d', $queue->getId()))
            ->setPageTitle('detail', fn(Queue $queue) => sprintf('队列详情 #%d', $queue->getId()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(50)
            ->setSearchFields(['id', 'emailSubject', 'errorMessage']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield FormField::addPanel('任务信息')
            ->setIcon('fa fa-tasks');

        yield AssociationField::new('task', '关联任务')
            ->setRequired(true)
            ->setColumns(6);

        yield AssociationField::new('receiver', '收件人')
            ->setRequired(true)
            ->setColumns(6);

        yield FormField::addPanel('邮件内容')
            ->setIcon('fa fa-envelope');

        yield TextField::new('emailSubject', '邮件主题')
            ->setRequired(true)
            ->setColumns(12);

        yield TextareaField::new('emailBody', '邮件内容')
            ->setRequired(true)
            ->hideOnIndex()
            ->setColumns(12)
            ->setFormTypeOption('attr', [
                'rows' => 10,
            ]);

        yield FormField::addPanel('发送信息')
            ->setIcon('fa fa-paper-plane');

        yield AssociationField::new('sender', '发送器')
            ->setRequired(true)
            ->setColumns(6);

        yield DateTimeField::new('sendTime', '发送时间')
            ->setColumns(6)
            ->hideOnForm();

        yield BooleanField::new('done', '已完成')
            ->setColumns(6)
            ->renderAsSwitch(false);

        yield TextareaField::new('errorMessage', '错误信息')
            ->hideOnIndex()
            ->setColumns(12)
            ->hideOnForm();

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
        $resendAction = Action::new('resendEmail', '重新发送')
            ->setIcon('fa fa-redo')
            ->setCssClass('btn btn-success')
            ->displayIf(static function (Queue $queue) {
                // 只有失败的邮件才能重新发送
                return $queue->isDone() && $queue->getErrorMessage() !== null;
            })
            ->linkToCrudAction('resendEmail');

        $viewTaskAction = Action::new('viewTask', '查看任务')
            ->setIcon('fa fa-tasks')
            ->setCssClass('btn btn-info')
            ->linkToCrudAction('viewTask');

        $viewReceiverAction = Action::new('viewReceiver', '查看收件人')
            ->setIcon('fa fa-user')
            ->setCssClass('btn btn-info')
            ->linkToCrudAction('viewReceiver');

        $viewBodyAction = Action::new('viewBody', '查看内容')
            ->setIcon('fa fa-file-alt')
            ->setCssClass('btn btn-info')
            ->linkToCrudAction('viewBody');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $resendAction)
            ->add(Crud::PAGE_DETAIL, $viewTaskAction)
            ->add(Crud::PAGE_DETAIL, $viewReceiverAction)
            ->add(Crud::PAGE_DETAIL, $viewBodyAction)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('task')
            ->add('receiver')
            ->add('sender')
            ->add('done')
            ->add('sendTime')
            ->add('createTime')
            ->add('valid');
    }

    /**
     * 重新发送邮件
     */
    #[AdminAction(routePath: '{entityId}/resend', routeName: 'resend_email')]
    public function resendEmail(AdminContext $context): Response
    {
        /** @var Queue $queue */
        $queue = $context->getEntity()->getInstance();

        // 重置队列状态
        $queue->setDone(false);
        $queue->setErrorMessage(null);
        $queue->setSendTime(null);
        $queue->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->persist($queue);
        $this->entityManager->flush();

        // 创建新的消息
        $message = new SendQueueEmailMessage();
        $message->setQueueId($queue->getId());
        $this->messageBus->dispatch($message);

        $this->addFlash('success', sprintf('邮件已加入发送队列，队列ID: %d', $queue->getId()));

        return $this->redirect($this->adminUrlGenerator
            ->setAction(Action::DETAIL)
            ->setEntityId($queue->getId())
            ->generateUrl());
    }

    /**
     * 查看关联任务
     */
    #[AdminAction(routePath: '{entityId}/view-task', routeName: 'view_task')]
    public function viewTask(AdminContext $context): Response
    {
        /** @var Queue $queue */
        $queue = $context->getEntity()->getInstance();

        if ($queue->getTask() === null) {
            $this->addFlash('warning', '此队列没有关联任务');
            return $this->redirect($context->getReferrer());
        }

        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(TaskCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($queue->getTask()->getId())
            ->generateUrl());
    }

    /**
     * 查看收件人
     */
    #[AdminAction(routePath: '{entityId}/view-receiver', routeName: 'view_receiver')]
    public function viewReceiver(AdminContext $context): Response
    {
        /** @var Queue $queue */
        $queue = $context->getEntity()->getInstance();

        if ($queue->getReceiver() === null) {
            $this->addFlash('warning', '此队列没有关联收件人');
            return $this->redirect($context->getReferrer());
        }

        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(ReceiverCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($queue->getReceiver()->getId())
            ->generateUrl());
    }

    /**
     * 查看邮件内容
     */
    #[AdminAction(routePath: '{entityId}/view-body', routeName: 'view_body')]
    public function viewBody(AdminContext $context): Response
    {
        /** @var Queue $queue */
        $queue = $context->getEntity()->getInstance();

        $subject = $queue->getEmailSubject() ?? '无主题';
        $body = $queue->getEmailBody() ?? '无内容';

        return new Response("
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$subject}</title>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .email-container { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
                .email-subject { font-size: 18px; font-weight: bold; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
                .email-body { line-height: 1.6; }
                .back-link { margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-subject'>主题: {$subject}</div>
                <div class='email-body'>{$body}</div>
            </div>
            <div class='back-link'>
                <a href='javascript:history.back()'>返回</a>
            </div>
        </body>
        </html>
        ");
    }
}
