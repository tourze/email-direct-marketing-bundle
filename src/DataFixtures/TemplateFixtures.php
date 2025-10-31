<?php

namespace EmailDirectMarketingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use EmailDirectMarketingBundle\Entity\Template;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class TemplateFixtures extends Fixture
{
    public const WELCOME_TEMPLATE_REFERENCE = 'welcome-template';
    public const PROMO_TEMPLATE_REFERENCE = 'promo-template';
    public const NEWSLETTER_TEMPLATE_REFERENCE = 'newsletter-template';

    public function load(ObjectManager $manager): void
    {
        $welcomeTemplate = new Template();
        $welcomeTemplate->setName('欢迎邮件模板');
        $welcomeTemplate->setSubject('欢迎加入我们的平台！');
        $welcomeTemplate->setHtmlBody('<h1>欢迎！</h1><p>感谢您注册我们的平台，希望您使用愉快。</p>');
        $welcomeTemplate->setValid(true);

        $manager->persist($welcomeTemplate);
        $this->addReference(self::WELCOME_TEMPLATE_REFERENCE, $welcomeTemplate);

        $promoTemplate = new Template();
        $promoTemplate->setName('促销邮件模板');
        $promoTemplate->setSubject('限时优惠活动开始啦！');
        $promoTemplate->setHtmlBody('<h1>特价优惠</h1><p>快来参加我们的限时优惠活动吧！</p>');
        $promoTemplate->setValid(true);

        $manager->persist($promoTemplate);
        $this->addReference(self::PROMO_TEMPLATE_REFERENCE, $promoTemplate);

        $newsletterTemplate = new Template();
        $newsletterTemplate->setName('新闻邮件模板');
        $newsletterTemplate->setSubject('本周新闻摘要');
        $newsletterTemplate->setHtmlBody('<h1>新闻摘要</h1><p>这里是本周的重要新闻内容。</p>');
        $newsletterTemplate->setValid(true);

        $manager->persist($newsletterTemplate);
        $this->addReference(self::NEWSLETTER_TEMPLATE_REFERENCE, $newsletterTemplate);

        $manager->flush();
    }
}
