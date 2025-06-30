<?php

namespace EmailDirectMarketingBundle\Tests\Integration\Controller\Admin;

use EmailDirectMarketingBundle\Controller\Admin\TemplateCrudController;
use EmailDirectMarketingBundle\Entity\Template;
use PHPUnit\Framework\TestCase;

class TemplateCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertSame(Template::class, TemplateCrudController::getEntityFqcn());
    }
}