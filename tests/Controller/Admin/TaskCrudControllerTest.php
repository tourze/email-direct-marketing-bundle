<?php

namespace EmailDirectMarketingBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EmailDirectMarketingBundle\Controller\Admin\TaskCrudController;
use EmailDirectMarketingBundle\Entity\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TaskCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TaskCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testControllerServiceExists(): void
    {
        $service = self::getContainer()->get(TaskCrudController::class);
        $this->assertInstanceOf(TaskCrudController::class, $service);
    }

    public function testUnauthorizedAccessBlocked(): void
    {
        $client = self::createClientWithDatabase();

        try {
            $client->request('GET', '/admin?crudAction=index&crudControllerFqcn=' . urlencode(TaskCrudController::class));
            $this->assertResponseRedirects('/login');
        } catch (AccessDeniedException $e) {
            // 访问被拒绝异常也是正确的安全行为
            $this->assertStringContainsString('Access Denied', $e->getMessage());
        }
    }

    /**
     * 提供Index页面预期显示的字段标签
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID header' => ['ID'];
        yield 'title header' => ['任务名称'];
        yield 'send tag header' => ['发送标签'];
        yield 'template header' => ['邮件模板'];
        yield 'status header' => ['状态'];
        yield 'start time header' => ['开始时间'];
        yield 'sender header' => ['发送器'];
        yield 'total count header' => ['总数量'];
        yield 'success count header' => ['成功数量'];
        yield 'failure count header' => ['失败数量'];
        yield 'created time header' => ['创建时间'];
        yield 'updated time header' => ['更新时间'];
        yield 'valid header' => ['有效'];
    }

    /**
     * 提供New页面预期显示的字段名（只包含基本input元素字段）
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title field' => ['title'];
        yield 'start time field' => ['startTime'];
        yield 'valid field' => ['valid'];
    }

    /**
     * 提供Edit页面预期显示的字段名（排除配置了hideOnForm的字段）
     *
     * 注意: 以下字段在Controller中配置了 hideOnForm()，因此不会在编辑页面显示：
     * - id (IdField with hideOnForm())
     * - totalCount (IntegerField with hideOnForm())
     * - successCount (IntegerField with hideOnForm())
     * - failureCount (IntegerField with hideOnForm())
     * - createTime (DateTimeField with hideOnForm())
     * - updateTime (DateTimeField with hideOnForm())
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 只包含在编辑页面实际显示的字段
        yield 'title field' => ['title'];
        yield 'tags field' => ['tags'];
        yield 'template field' => ['template'];
        yield 'status field' => ['status'];
        yield 'start time field' => ['startTime'];
        yield 'senders field' => ['senders'];
        yield 'valid field' => ['valid'];
    }

    /**
     * 重写父类方法，验证数据提供器与实际字段配置的一致性
     */

    /**
     * 测试立即执行任务动作方法存在
     */
    public function testExecuteTask(): void
    {
        $client = self::createClientWithDatabase();

        // 测试基本的控制器服务存在
        $service = self::getContainer()->get(TaskCrudController::class);
        $this->assertInstanceOf(TaskCrudController::class, $service);

        // 验证控制器有 executeTask 方法
        $reflection = new \ReflectionClass(TaskCrudController::class);
        $this->assertTrue($reflection->hasMethod('executeTask'));

        $method = $reflection->getMethod('executeTask');
        $this->assertTrue($method->isPublic());
    }

    /**
     * 测试重置任务动作方法存在
     */
    public function testResetTask(): void
    {
        $client = self::createClientWithDatabase();

        // 测试基本的控制器服务存在
        $service = self::getContainer()->get(TaskCrudController::class);
        $this->assertInstanceOf(TaskCrudController::class, $service);

        // 验证控制器有 resetTask 方法
        $reflection = new \ReflectionClass(TaskCrudController::class);
        $this->assertTrue($reflection->hasMethod('resetTask'));

        $method = $reflection->getMethod('resetTask');
        $this->assertTrue($method->isPublic());
    }

    /**
     * 测试查看队列动作方法存在
     */
    public function testViewQueues(): void
    {
        $client = self::createClientWithDatabase();

        // 测试基本的控制器服务存在
        $service = self::getContainer()->get(TaskCrudController::class);
        $this->assertInstanceOf(TaskCrudController::class, $service);

        // 验证控制器有 viewQueues 方法
        $reflection = new \ReflectionClass(TaskCrudController::class);
        $this->assertTrue($reflection->hasMethod('viewQueues'));

        $method = $reflection->getMethod('viewQueues');
        $this->assertTrue($method->isPublic());
    }

    /**
     * 获取TaskCrudController服务实例
     *
     * @return AbstractCrudController<Task>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TaskCrudController::class);
    }
}
