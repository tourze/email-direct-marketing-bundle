<?php

namespace EmailDirectMarketingBundle\Tests\Integration\Controller\Admin;

use EmailDirectMarketingBundle\Controller\Admin\ReceiverCrudController;
use EmailDirectMarketingBundle\Entity\Receiver;
use PHPUnit\Framework\TestCase;

class ReceiverCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Receiver::class, ReceiverCrudController::getEntityFqcn());
    }
}