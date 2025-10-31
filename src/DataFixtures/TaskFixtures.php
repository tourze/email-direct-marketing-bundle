<?php

namespace EmailDirectMarketingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public const WELCOME_TASK_REFERENCE = 'welcome-task';
    public const PROMO_TASK_REFERENCE = 'promo-task';
    public const FINISHED_TASK_REFERENCE = 'finished-task';

    public function load(ObjectManager $manager): void
    {
        $welcomeTemplate = $this->getReference(TemplateFixtures::WELCOME_TEMPLATE_REFERENCE, Template::class);
        $promoTemplate = $this->getReference(TemplateFixtures::PROMO_TEMPLATE_REFERENCE, Template::class);
        $newsletterTemplate = $this->getReference(TemplateFixtures::NEWSLETTER_TEMPLATE_REFERENCE, Template::class);

        $mainSender = $this->getReference(SenderFixtures::MAIN_SENDER_REFERENCE, Sender::class);
        $marketingSender = $this->getReference(SenderFixtures::MARKETING_SENDER_REFERENCE, Sender::class);

        $welcomeTask = new Task();
        $welcomeTask->setTitle('新用户欢迎邮件任务');
        $welcomeTask->setTags(['welcome', 'new-user']);
        $welcomeTask->setTemplate($welcomeTemplate);
        $welcomeTask->setStatus(TaskStatus::WAITING);
        $welcomeTask->setStartTime(new \DateTimeImmutable('+1 hour'));
        $welcomeTask->addSender($mainSender);
        $welcomeTask->setValid(true);

        $manager->persist($welcomeTask);
        $this->addReference(self::WELCOME_TASK_REFERENCE, $welcomeTask);

        $promoTask = new Task();
        $promoTask->setTitle('促销活动邮件任务');
        $promoTask->setTags(['promo', 'marketing']);
        $promoTask->setTemplate($promoTemplate);
        $promoTask->setStatus(TaskStatus::SENDING);
        $promoTask->setTotalCount(1000);
        $promoTask->setSuccessCount(800);
        $promoTask->setFailureCount(50);
        $promoTask->setStartTime(new \DateTimeImmutable('-2 hours'));
        $promoTask->addSender($marketingSender);
        $promoTask->setValid(true);

        $manager->persist($promoTask);
        $this->addReference(self::PROMO_TASK_REFERENCE, $promoTask);

        $finishedTask = new Task();
        $finishedTask->setTitle('已完成的新闻邮件任务');
        $finishedTask->setTags(['newsletter']);
        $finishedTask->setTemplate($newsletterTemplate);
        $finishedTask->setStatus(TaskStatus::FINISHED);
        $finishedTask->setTotalCount(500);
        $finishedTask->setSuccessCount(480);
        $finishedTask->setFailureCount(20);
        $finishedTask->setStartTime(new \DateTimeImmutable('-1 day'));
        $finishedTask->addSender($mainSender);
        $finishedTask->setValid(true);

        $manager->persist($finishedTask);
        $this->addReference(self::FINISHED_TASK_REFERENCE, $finishedTask);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TemplateFixtures::class,
            SenderFixtures::class,
        ];
    }
}
