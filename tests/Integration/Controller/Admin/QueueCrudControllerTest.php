<?php

namespace EmailDirectMarketingBundle\Tests\Integration\Controller\Admin;

use EmailDirectMarketingBundle\Controller\Admin\QueueCrudController;
use EmailDirectMarketingBundle\Entity\Queue;
use PHPUnit\Framework\TestCase;

class QueueCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Queue::class, QueueCrudController::getEntityFqcn());
    }
}