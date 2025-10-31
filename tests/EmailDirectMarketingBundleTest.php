<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Tests;

use EmailDirectMarketingBundle\EmailDirectMarketingBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(EmailDirectMarketingBundle::class)]
#[RunTestsInSeparateProcesses]
final class EmailDirectMarketingBundleTest extends AbstractBundleTestCase
{
}
