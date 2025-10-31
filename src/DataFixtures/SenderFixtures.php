<?php

namespace EmailDirectMarketingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use EmailDirectMarketingBundle\Entity\Sender;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class SenderFixtures extends Fixture
{
    public const MAIN_SENDER_REFERENCE = 'main-sender';
    public const SUPPORT_SENDER_REFERENCE = 'support-sender';
    public const MARKETING_SENDER_REFERENCE = 'marketing-sender';

    public function load(ObjectManager $manager): void
    {
        $mainSender = new Sender();
        $mainSender->setTitle('主发送器');
        $mainSender->setDsn('smtp://user:pass@smtp.test.local:587');
        $mainSender->setSenderName('系统邮件');
        $mainSender->setEmailAddress('noreply@test.local');
        $mainSender->setValid(true);

        $manager->persist($mainSender);
        $this->addReference(self::MAIN_SENDER_REFERENCE, $mainSender);

        $supportSender = new Sender();
        $supportSender->setTitle('客服发送器');
        $supportSender->setDsn('smtp://support:pass@smtp.test.local:587');
        $supportSender->setSenderName('客服团队');
        $supportSender->setEmailAddress('support@test.local');
        $supportSender->setValid(true);

        $manager->persist($supportSender);
        $this->addReference(self::SUPPORT_SENDER_REFERENCE, $supportSender);

        $marketingSender = new Sender();
        $marketingSender->setTitle('营销发送器');
        $marketingSender->setDsn('smtp://marketing:pass@smtp.test.local:587');
        $marketingSender->setSenderName('营销团队');
        $marketingSender->setEmailAddress('marketing@test.local');
        $marketingSender->setValid(true);

        $manager->persist($marketingSender);
        $this->addReference(self::MARKETING_SENDER_REFERENCE, $marketingSender);

        $manager->flush();
    }
}
