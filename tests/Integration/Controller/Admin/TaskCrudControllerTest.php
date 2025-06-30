<?php

namespace EmailDirectMarketingBundle\Tests\Integration\Controller\Admin;

use EmailDirectMarketingBundle\Controller\Admin\TaskCrudController;
use EmailDirectMarketingBundle\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Task::class, TaskCrudController::getEntityFqcn());
    }
}