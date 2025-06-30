<?php

namespace EmailDirectMarketingBundle\Tests\Integration\Controller\Admin;

use EmailDirectMarketingBundle\Controller\Admin\SenderCrudController;
use EmailDirectMarketingBundle\Entity\Sender;
use PHPUnit\Framework\TestCase;

class SenderCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Sender::class, SenderCrudController::getEntityFqcn());
    }
}