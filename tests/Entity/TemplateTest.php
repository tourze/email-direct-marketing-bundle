<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Template::class)]
final class TemplateTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Template();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'Test Template'];
        yield 'subject' => ['subject', 'Test Subject'];
        yield 'htmlBody' => ['htmlBody', '<p>Test Body</p>'];
        yield 'valid' => ['valid', true];
    }

    public function testInitialState(): void
    {
        $template = new Template();

        $this->assertSame(0, $template->getId());
        $this->assertEmpty($template->getName());
        $this->assertEmpty($template->getSubject());
        $this->assertEmpty($template->getHtmlBody());
        $this->assertFalse($template->isValid());
        $this->assertNull($template->getCreateTime());
        $this->assertNull($template->getUpdateTime());
    }

    public function testSetNameGetName(): void
    {
        $template = new Template();

        $name = '测试模板';
        $template->setName($name);
        $this->assertSame($name, $template->getName());
    }

    public function testSetSubjectGetSubject(): void
    {
        $template = new Template();

        $subject = '邮件标题测试';
        $template->setSubject($subject);
        $this->assertSame($subject, $template->getSubject());
    }

    public function testSetHtmlBodyGetHtmlBody(): void
    {
        $template = new Template();

        $body = '<p>这是一个<strong>HTML</strong>邮件模板</p>';
        $template->setHtmlBody($body);
        $this->assertSame($body, $template->getHtmlBody());
    }

    public function testSetValidIsValid(): void
    {
        $template = new Template();

        $this->assertFalse($template->isValid());

        $template->setValid(true);
        $this->assertTrue($template->isValid());

        $template->setValid(false);
        $this->assertFalse($template->isValid());
    }

    public function testSetCreateTimeGetCreateTime(): void
    {
        $template = new Template();

        $now = new \DateTimeImmutable();
        $template->setCreateTime($now);
        $this->assertSame($now, $template->getCreateTime());
    }

    public function testSetUpdateTimeGetUpdateTime(): void
    {
        $template = new Template();

        $now = new \DateTimeImmutable();
        $template->setUpdateTime($now);
        $this->assertSame($now, $template->getUpdateTime());
    }

    public function testToString(): void
    {
        $template = new Template();

        $name = '测试模板';
        $template->setName($name);
        $this->assertSame($name, (string) $template);
    }
}
