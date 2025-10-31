<?php

namespace EmailDirectMarketingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use EmailDirectMarketingBundle\Entity\Receiver;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class ReceiverFixtures extends Fixture
{
    public const VIP_RECEIVER_REFERENCE = 'vip-receiver';
    public const REGULAR_RECEIVER_REFERENCE = 'regular-receiver';
    public const UNSUBSCRIBED_RECEIVER_REFERENCE = 'unsubscribed-receiver';

    public function load(ObjectManager $manager): void
    {
        $vipReceiver = new Receiver();
        $vipReceiver->setName('VIP用户张三');
        $vipReceiver->setEmailAddress('vip@test.local');
        $vipReceiver->setTags(['vip', 'active']);
        $vipReceiver->setUnsubscribed(false);

        $manager->persist($vipReceiver);
        $this->addReference(self::VIP_RECEIVER_REFERENCE, $vipReceiver);

        $regularReceiver = new Receiver();
        $regularReceiver->setName('普通用户李四');
        $regularReceiver->setEmailAddress('user@test.local');
        $regularReceiver->setTags(['regular']);
        $regularReceiver->setUnsubscribed(false);
        $regularReceiver->setLastSendTime(new \DateTimeImmutable('-1 week'));

        $manager->persist($regularReceiver);
        $this->addReference(self::REGULAR_RECEIVER_REFERENCE, $regularReceiver);

        $unsubscribedReceiver = new Receiver();
        $unsubscribedReceiver->setName('已退订用户王五');
        $unsubscribedReceiver->setEmailAddress('unsubscribed@test.local');
        $unsubscribedReceiver->setTags(['regular']);
        $unsubscribedReceiver->setUnsubscribed(true);
        $unsubscribedReceiver->setLastSendTime(new \DateTimeImmutable('-1 month'));

        $manager->persist($unsubscribedReceiver);
        $this->addReference(self::UNSUBSCRIBED_RECEIVER_REFERENCE, $unsubscribedReceiver);

        $manager->flush();
    }
}
