# Email Direct Marketing Bundle

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

A Symfony bundle for managing email marketing campaigns with support for 
bulk email sending, template management, and detailed tracking.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Console Commands](#console-commands)
- [Admin Interface](#admin-interface)
- [Advanced Usage](#advanced-usage)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)
- [References](#references)

## Features

- **Campaign Management**: Create and manage email marketing campaigns with 
  customizable templates
- **Receiver Management**: Organize receivers with tags for targeted campaigns
- **Sender Pool**: Support multiple sender email addresses with rotation
- **Template System**: Rich HTML email templates with variable substitution
- **Queue System**: Asynchronous email sending with Symfony Messenger
- **Admin Interface**: Complete EasyAdmin integration for managing campaigns
- **Tracking**: Monitor campaign performance with success/failure statistics
- **Scheduling**: Schedule campaigns to start at specific times
- **Tag-based Targeting**: Send emails to receivers based on tag filtering

## Requirements

- PHP 8.1 or higher
- Symfony 7.3 or higher
- Doctrine ORM 3.0 or higher
- Symfony Messenger component
- Symfony Mailer component

## Installation

```bash
composer require tourze/email-direct-marketing-bundle
```

## Configuration

### Messenger Transport

Configure Messenger to handle email queue processing:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            EmailDirectMarketingBundle\Message\SendQueueEmailMessage: async
```

### Database Schema

The bundle will create the following tables:
- `ims_edm_task` - Email marketing campaigns
- `ims_edm_template` - Email templates
- `ims_edm_sender` - Sender email addresses
- `ims_edm_receiver` - Email recipients
- `ims_edm_queue` - Email sending queue

## Quick Start

### Enable the Bundle

Register the bundle in your `bundles.php`:

```php
return [
    // ...
    EmailDirectMarketingBundle\EmailDirectMarketingBundle::class => ['all' => true],
];
```

#### Configure Database

Run migrations to create the required database tables:

```bash
php bin/console doctrine:migrations:migrate
```

#### Configure Messenger

Add the message handler to your messenger configuration:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
        routing:
            EmailDirectMarketingBundle\Message\SendQueueEmailMessage: async
```

#### Basic Usage

Create a campaign through the admin interface or programmatically:

```php
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;

// Create a template
$template = new Template();
$template->setName('Welcome Email');
$template->setSubject('Welcome to ${receiver.getName()}!');
$template->setHtmlBody('<h1>Hello ${receiver.getName()}</h1>');

// Create a task
$task = new Task();
$task->setTitle('Welcome Campaign');
$task->setTemplate($template);
$task->setTags(['new-users']);
$task->setStartTime(new \DateTimeImmutable('+1 hour'));
$task->setStatus(TaskStatus::WAITING);
```

## Console Commands

### edm:start-task

Check and start email marketing tasks that are ready to be sent.

```bash
# Check and start all pending tasks
php bin/console edm:start-task

# Start a specific task
php bin/console edm:start-task --task-id=123

# Force start a task (ignore start time)
php bin/console edm:start-task --task-id=123 --force
```

This command is designed to be run as a cron job (every minute) to 
automatically process scheduled campaigns.

## Admin Interface

The bundle provides EasyAdmin controllers for managing:

- **Tasks**: Create and monitor email campaigns
- **Templates**: Design email templates with variable substitution
- **Senders**: Manage sender email addresses
- **Receivers**: Organize email recipients with tags
- **Queues**: Monitor email sending queue and status

## Template Variables

Templates support variable substitution using the `${expression}` syntax:

- `${receiver.getName()}` - Receiver's name
- `${receiver.getEmailAddress()}` - Receiver's email
- `${task.getTitle()}` - Campaign title
- `${now.format('Y-m-d')}` - Current date

## Advanced Usage

### Custom Variable Providers

You can extend the template system with custom variables by creating a service 
that implements variable providers:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CustomVariableProvider
{
    public function addVariables(ExpressionLanguage $expressionLanguage): void
    {
        $expressionLanguage->register('customVar', 
            function () {
                return 'your custom value';
            },
            function ($arguments) {
                return 'your custom value';
            }
        );
    }
}
```

#### Queue Processing Optimization

For high-volume campaigns, consider optimizing queue processing:

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

#### Monitoring and Logging

The bundle integrates with Symfony's logging system:

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

## Architecture

The bundle follows a queue-based architecture:

1. **Task Creation**: Define campaign with template, tags, and schedule
2. **Queue Generation**: When task starts, create queue items for each 
   matching receiver
3. **Async Processing**: Queue items are processed by message handlers
4. **Status Tracking**: Monitor success/failure for each email

## Security

### Email Validation

The bundle includes built-in email validation:
- Receiver email addresses are validated before adding to queue
- Sender email addresses must be verified
- DMARC/SPF records are recommended for production use

#### Rate Limiting

Consider implementing rate limiting for your SMTP provider:

```yaml
# config/packages/mailer.yaml
framework:
    mailer:
        transports:
            main: '%env(MAILER_DSN)%'
        envelope:
            sender: 'noreply@example.com'
```

#### Data Protection

- Receiver data is encrypted at rest
- Email content can be sanitized before storage
- Unsubscribe functionality is built-in

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## References

- [Sending Emails with Mailer](https://symfony.com/doc/current/mailer.html)
- [Email Authentication and Trust](https://buaq.net/go-141400.html)
- [DMARC Configuration Guide](https://service.exmail.qq.com/cgi-bin/help?subtype=1&no=1001520&id=16)
- [Email Spam Testing](https://www.mail-tester.com/)