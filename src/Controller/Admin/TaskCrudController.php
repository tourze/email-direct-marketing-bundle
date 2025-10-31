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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Service\TaskService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Task>
 */
#[Autoconfigure(public: true)]
#[AdminCrud(routePath: '/email-marketing/task', routeName: 'email_marketing_task')]
final class TaskCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ?AdminUrlGenerator $adminUrlGenerator,
        private readonly TaskService $taskService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('邮件营销任务')
            ->setEntityLabelInPlural('邮件营销任务')
            ->setPageTitle('index', '邮件营销任务列表')
            ->setPageTitle('new', '创建邮件营销任务')
            ->setPageTitle('edit', fn (Task $task) => sprintf('编辑任务 #%d: %s', $task->getId(), $task->getTitle()))
            ->setPageTitle('detail', fn (Task $task) => sprintf('任务详情 #%d: %s', $task->getId(), $task->getTitle()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'tags'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield FormField::addPanel('基本信息')
            ->setIcon('fa fa-info-circle')
        ;

        yield TextField::new('title', '任务名称')
            ->setColumns(12)
            ->setHelp('输入任务的名称，便于识别和管理')
        ;

        yield ArrayField::new('tags', '发送标签')
            ->setColumns(12)
            ->setHelp('收件人需要匹配这些标签才会收到邮件')
        ;

        yield AssociationField::new('template', '邮件模板')
            ->setColumns(12)
            ->setFormTypeOptions([
                'placeholder' => '选择邮件模板',
            ])
        ;

        yield ChoiceField::new('status', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => TaskStatus::class,
            ])
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof TaskStatus ? $value->getLabel() : '';
            })
        ;

        yield DateTimeField::new('startTime', '开始时间')
            ->setColumns(6)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('任务会在此时间开始执行')
        ;

        yield FormField::addPanel('关联发送器')
            ->setIcon('fa fa-envelope')
        ;

        yield AssociationField::new('senders', '发送器')
            ->setColumns(12)
            ->setFormTypeOption('by_reference', false)
            ->setHelp('可以选择多个发送器，系统会随机使用其中一个发送邮件')
        ;

        yield FormField::addPanel('任务执行统计')
            ->setIcon('fa fa-chart-bar')
            ->hideOnForm()
        ;

        yield IntegerField::new('totalCount', '总数量')
            ->hideOnForm()
        ;

        yield IntegerField::new('successCount', '成功数量')
            ->hideOnForm()
        ;

        yield IntegerField::new('failureCount', '失败数量')
            ->hideOnForm()
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

        yield BooleanField::new('valid', '有效')
            ->setColumns(12)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $executeAction = Action::new('executeTask', '立即执行')
            ->setIcon('fa fa-play')
            ->setCssClass('btn btn-success')
            ->linkToCrudAction('executeTask')
        ;

        $resetAction = Action::new('resetTask', '重置任务')
            ->setIcon('fa fa-redo')
            ->setCssClass('btn btn-warning')
            ->linkToCrudAction('resetTask')
        ;

        $viewQueuesAction = Action::new('viewQueues', '查看队列')
            ->setIcon('fa fa-list')
            ->setCssClass('btn btn-primary')
            ->linkToCrudAction('viewQueues')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $executeAction)
            ->add(Crud::PAGE_DETAIL, $resetAction)
            ->add(Crud::PAGE_DETAIL, $viewQueuesAction)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('status')
            ->add('createTime')
            ->add('valid')
        ;
    }

    /**
     * 立即执行任务
     */
    #[AdminAction(routePath: '{entityId}/execute', routeName: 'execute_task')]
    public function executeTask(AdminContext $context, Request $request): Response
    {
        $task = $context->getEntity()->getInstance();
        assert($task instanceof Task);

        // 检查任务状态
        if (TaskStatus::WAITING !== $task->getStatus()) {
            $this->addFlash('warning', sprintf(
                '任务 #%d 当前状态为 %s，无法执行',
                $task->getId(),
                $task->getStatus()?->getLabel() ?? 'Unknown'
            ));

            return $this->redirect($this->adminUrlGenerator
                ?->setAction(Action::DETAIL)
                ->setEntityId($task->getId())
                ->generateUrl() ?? '/');
        }

        // 设置开始时间为现在
        $task->setStartTime(new \DateTimeImmutable());
        $task->setStatus(TaskStatus::SENDING);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        // 异步执行任务
        $this->taskService->createQueue($task);

        $this->addFlash('success', sprintf('任务 #%d 已开始执行', $task->getId()));

        return $this->redirect($this->adminUrlGenerator
            ?->setAction(Action::DETAIL)
            ->setEntityId($task->getId())
            ->generateUrl() ?? '/');
    }

    /**
     * 重置任务状态
     */
    #[AdminAction(routePath: '{entityId}/reset', routeName: 'reset_task')]
    public function resetTask(AdminContext $context, Request $request): Response
    {
        $task = $context->getEntity()->getInstance();
        assert($task instanceof Task);

        // 只有完成的任务才能重置
        if (TaskStatus::FINISHED !== $task->getStatus()) {
            $this->addFlash('warning', sprintf(
                '只有已完成的任务才能重置，当前状态为: %s',
                $task->getStatus()?->getLabel() ?? 'Unknown'
            ));

            return $this->redirect($this->adminUrlGenerator
                ?->setAction(Action::DETAIL)
                ->setEntityId($task->getId())
                ->generateUrl() ?? '/');
        }

        // 重置任务状态
        $task->setStatus(TaskStatus::WAITING);
        $task->setTotalCount(null);
        $task->setSuccessCount(null);
        $task->setFailureCount(null);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('任务 #%d 已重置为等待发送状态', $task->getId()));

        return $this->redirect($this->adminUrlGenerator
            ?->setAction(Action::DETAIL)
            ->setEntityId($task->getId())
            ->generateUrl() ?? '/');
    }

    /**
     * 查看任务相关的队列
     */
    #[AdminAction(routePath: '{entityId}/queues', routeName: 'view_task_queues')]
    public function viewQueues(AdminContext $context, Request $request): Response
    {
        $task = $context->getEntity()->getInstance();
        assert($task instanceof Task);

        return $this->redirect($this->adminUrlGenerator
            ?->unsetAll()
            ->setController(QueueCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[task][comparison]', '=')
            ->set('filters[task][value]', $task->getId())
            ->generateUrl() ?? '/');
    }
}
