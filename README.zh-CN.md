# 邮件营销模块

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/email-direct-marketing-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/email-direct-marketing-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/email-direct-marketing-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/email-direct-marketing-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/email-direct-marketing-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/email-direct-marketing-bundle)
[![License](https://img.shields.io/packagist/l/tourze/email-direct-marketing-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/email-direct-marketing-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](
https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](
https://codecov.io/gh/tourze/php-monorepo)

一个用于管理邮件营销活动的 Symfony 模块，支持批量邮件发送、
模板管理和详细的跟踪功能。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [控制台命令](#控制台命令)
- [管理界面](#管理界面)
- [高级用法](#高级用法)
- [安全性](#安全性)
- [贡献](#贡献)
- [许可证](#许可证)
- [参考文档](#参考文档)

## 功能特性

- **营销活动管理**：创建和管理带有可自定义模板的邮件营销活动
- **收件人管理**：使用标签组织收件人，实现定向营销
- **发件人池**：支持多个发件人邮箱地址轮换发送
- **模板系统**：支持变量替换的富文本 HTML 邮件模板
- **队列系统**：使用 Symfony Messenger 异步发送邮件
- **管理界面**：完整的 EasyAdmin 集成，方便管理营销活动
- **跟踪统计**：监控营销活动的成功/失败统计信息
- **定时发送**：设置营销活动在指定时间开始
- **基于标签的定向发送**：根据标签筛选向收件人发送邮件

## 系统要求

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM 3.0 或更高版本
- Symfony Messenger 组件
- Symfony Mailer 组件

## 安装

```bash
composer require tourze/email-direct-marketing-bundle
```

## 配置

### Messenger 传输配置

配置 Messenger 来处理邮件队列：

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            EmailDirectMarketingBundle\Message\SendQueueEmailMessage: async
```

### 数据库结构

模块将创建以下数据表：
- `ims_edm_task` - 邮件营销活动
- `ims_edm_template` - 邮件模板
- `ims_edm_sender` - 发件人邮箱地址
- `ims_edm_receiver` - 邮件接收者
- `ims_edm_queue` - 邮件发送队列

## 快速开始

### 启用模块

在 `bundles.php` 中注册模块：

```php
return [
    // ...
    EmailDirectMarketingBundle\EmailDirectMarketingBundle::class => ['all' => true],
];
```

#### 配置数据库

运行迁移以创建所需的数据库表：

```bash
php bin/console doctrine:migrations:migrate
```

#### 配置 Messenger

在 messenger 配置中添加消息处理器：

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            EmailDirectMarketingBundle\Message\SendQueueEmailMessage: async
```

#### 基本使用

通过管理界面或编程方式创建营销活动：

```php
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;

// 创建模板
$template = new Template();
$template->setName('欢迎邮件');
$template->setSubject('欢迎您，${receiver.getName()}！');
$template->setHtmlBody('<h1>你好，${receiver.getName()}</h1>');

// 创建任务
$task = new Task();
$task->setTitle('欢迎营销活动');
$task->setTemplate($template);
$task->setTags(['新用户']);
$task->setStartTime(new \DateTimeImmutable('+1 hour'));
$task->setStatus(TaskStatus::WAITING);
```

## 控制台命令

### edm:start-task

检查并开始准备发送的邮件营销任务。

```bash
# 检查并开始所有待处理的任务
php bin/console edm:start-task

# 开始特定的任务
php bin/console edm:start-task --task-id=123

# 强制开始任务（忽略开始时间检查）
php bin/console edm:start-task --task-id=123 --force
```

此命令设计为定时任务（每分钟运行一次）以自动处理计划的营销活动。

## 管理界面

模块提供了 EasyAdmin 控制器来管理：

- **任务**：创建和监控邮件营销活动
- **模板**：设计支持变量替换的邮件模板
- **发件人**：管理发件人邮箱地址
- **收件人**：使用标签组织邮件接收者
- **队列**：监控邮件发送队列和状态

## 模板变量

模板支持使用 `${expression}` 语法进行变量替换：

- `${receiver.getName()}` - 收件人姓名
- `${receiver.getEmailAddress()}` - 收件人邮箱
- `${task.getTitle()}` - 营销活动标题
- `${now.format('Y-m-d')}` - 当前日期

## 高级用法

### 自定义变量提供器

您可以通过创建实现变量提供器的服务来扩展模板系统：

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CustomVariableProvider
{
    public function addVariables(ExpressionLanguage $expressionLanguage): void
    {
        $expressionLanguage->register('customVar', 
            function () {
                return '您的自定义值';
            },
            function ($arguments) {
                return '您的自定义值';
            }
        );
    }
}
```

#### 队列处理优化

对于大批量营销活动，考虑优化队列处理：

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    max_retries: 3
                    delay: 1000
```

#### 监控和日志

模块与 Symfony 的日志系统集成：

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        email_marketing:
            type: rotating_file
            path: '%kernel.logs_dir%/email_marketing.log'
            level: info
            channels: ['email_marketing']
```

## 架构设计

模块采用基于队列的架构：

1. **任务创建**：定义包含模板、标签和计划的营销活动
2. **队列生成**：任务开始时，为每个匹配的收件人创建队列项
3. **异步处理**：队列项由消息处理器处理
4. **状态跟踪**：监控每封邮件的成功/失败状态

## 安全性

### 邮箱验证

模块包含内置的邮箱验证：
- 收件人邮箱地址在添加到队列前会被验证
- 发件人邮箱地址必须经过验证
- 建议在生产环境中配置 DMARC/SPF 记录

#### 发送频率限制

考虑为您的 SMTP 提供商实施频率限制：

```yaml
# config/packages/mailer.yaml
framework:
    mailer:
        transports:
            main: '%env(MAILER_DSN)%'
        envelope:
            sender: 'noreply@example.com'
```

#### 数据保护

- 收件人数据在存储时加密
- 邮件内容可在存储前进行清理
- 内置退订功能

## 贡献

详情请参阅 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 许可证

MIT 许可证 (MIT)。详情请参阅 [许可证文件](LICENSE)。

## 参考文档

- [使用 Mailer 发送邮件](https://symfony.com/doc/current/mailer.html)
- [邮件可信其一邮件身份可信](https://buaq.net/go-141400.html)
- [什么是DMARC？如何设置企业邮箱的DMARC呢？](
https://service.exmail.qq.com/cgi-bin/help?subtype=1&no=1001520&id=16)
- [测试你发出邮件的垃圾邮件匹配度](https://www.mail-tester.com/)
- [搭建百万级别邮件发送平台](https://blog.it2048.cn/article-edm/)
- [EDM邮件定时群发营销系统开发](https://market.cloud.tencent.com/products/28586)
- [魔众EDM邮件营销系统](https://www.tecmz.com/product/edm)