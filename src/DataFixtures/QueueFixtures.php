<?php

namespace EmailDirectMarketingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class QueueFixtures extends Fixture implements DependentFixtureInterface
{
    public const SUCCESS_QUEUE_REFERENCE = 'success-queue';
    public const PENDING_QUEUE_REFERENCE = 'pending-queue';
    public const FAILED_QUEUE_REFERENCE = 'failed-queue';

    public function load(ObjectManager $manager): void
    {
        $promoTask = $this->getReference(TaskFixtures::PROMO_TASK_REFERENCE, Task::class);
        $welcomeTask = $this->getReference(TaskFixtures::WELCOME_TASK_REFERENCE, Task::class);

        $vipReceiver = $this->getReference(ReceiverFixtures::VIP_RECEIVER_REFERENCE, Receiver::class);
        $regularReceiver = $this->getReference(ReceiverFixtures::REGULAR_RECEIVER_REFERENCE, Receiver::class);

        $mainSender = $this->getReference(SenderFixtures::MAIN_SENDER_REFERENCE, Sender::class);
        $marketingSender = $this->getReference(SenderFixtures::MARKETING_SENDER_REFERENCE, Sender::class);

        $successQueue = new Queue();
        $successQueue->setTask($promoTask);
        $successQueue->setReceiver($vipReceiver);
        $successQueue->setSender($marketingSender);
        $successQueue->setEmailSubject('限时优惠活动开始啦！');
        $successQueue->setEmailBody('<h1>特价优惠</h1><p>快来参加我们的限时优惠活动吧！</p>');
        $successQueue->setSendTime(new \DateTimeImmutable('-1 hour'));
        $successQueue->setDone(true);
        $successQueue->setValid(true);

        $manager->persist($successQueue);
        $this->addReference(self::SUCCESS_QUEUE_REFERENCE, $successQueue);

        $pendingQueue = new Queue();
        $pendingQueue->setTask($welcomeTask);
        $pendingQueue->setReceiver($regularReceiver);
        $pendingQueue->setSender($mainSender);
        $pendingQueue->setEmailSubject('欢迎加入我们的平台！');
        $pendingQueue->setEmailBody('<h1>欢迎！</h1><p>感谢您注册我们的平台，希望您使用愉快。</p>');
        $pendingQueue->setDone(false);
        $pendingQueue->setValid(true);

        $manager->persist($pendingQueue);
        $this->addReference(self::PENDING_QUEUE_REFERENCE, $pendingQueue);

        $failedQueue = new Queue();
        $failedQueue->setTask($promoTask);
        $failedQueue->setReceiver($regularReceiver);
        $failedQueue->setSender($marketingSender);
        $failedQueue->setEmailSubject('限时优惠活动开始啦！');
        $failedQueue->setEmailBody('<h1>特价优惠</h1><p>快来参加我们的限时优惠活动吧！</p>');
        $failedQueue->setDone(true);
        $failedQueue->setErrorMessage('SMTP Error: Could not connect to host');
        $failedQueue->setValid(false);

        $manager->persist($failedQueue);
        $this->addReference(self::FAILED_QUEUE_REFERENCE, $failedQueue);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TaskFixtures::class,
            ReceiverFixtures::class,
            SenderFixtures::class,
        ];
    }
}
