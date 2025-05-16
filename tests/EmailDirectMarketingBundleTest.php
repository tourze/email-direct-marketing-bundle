<?php

namespace EmailDirectMarketingBundle\Tests;

use EmailDirectMarketingBundle\EmailDirectMarketingBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmailDirectMarketingBundleTest extends TestCase
{
    public function test_bundle_instantiation(): void
    {
        $bundle = new EmailDirectMarketingBundle();
        
        $this->assertInstanceOf(Bundle::class, $bundle);
        $this->assertInstanceOf(EmailDirectMarketingBundle::class, $bundle);
    }
} 