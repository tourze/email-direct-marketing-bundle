<?php

namespace EmailDirectMarketingBundle\Tests\Entity;

use EmailDirectMarketingBundle\Entity\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    private Template $template;

    protected function setUp(): void
    {
        $this->template = new Template();
    }

    public function test_initialState(): void
    {
        $this->assertSame(0, $this->template->getId());
        $this->assertEmpty($this->template->getName());
        $this->assertEmpty($this->template->getSubject());
        $this->assertEmpty($this->template->getHtmlBody());
        $this->assertFalse($this->template->isValid());
        $this->assertNull($this->template->getCreateTime());
        $this->assertNull($this->template->getUpdateTime());
    }

    public function test_setName_getName(): void
    {
        $name = '测试模板';
        $this->template->setName($name);
        $this->assertSame($name, $this->template->getName());
    }

    public function test_setSubject_getSubject(): void
    {
        $subject = '邮件标题测试';
        $this->template->setSubject($subject);
        $this->assertSame($subject, $this->template->getSubject());
    }

    public function test_setHtmlBody_getHtmlBody(): void
    {
        $body = '<p>这是一个<strong>HTML</strong>邮件模板</p>';
        $this->template->setHtmlBody($body);
        $this->assertSame($body, $this->template->getHtmlBody());
    }

    public function test_setValid_isValid(): void
    {
        $this->assertFalse($this->template->isValid());
        
        $this->template->setValid(true);
        $this->assertTrue($this->template->isValid());
        
        $this->template->setValid(false);
        $this->assertFalse($this->template->isValid());
    }

    public function test_setCreateTime_getCreateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->template->setCreateTime($now);
        $this->assertSame($now, $this->template->getCreateTime());
    }

    public function test_setUpdateTime_getUpdateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->template->setUpdateTime($now);
        $this->assertSame($now, $this->template->getUpdateTime());
    }

    public function test_toString(): void
    {
        $name = '测试模板';
        $this->template->setName($name);
        $this->assertSame($name, (string) $this->template);
    }
} 