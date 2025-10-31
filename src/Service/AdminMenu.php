<?php

namespace EmailDirectMarketingBundle\Service;

use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('邮件营销')) {
            $item->addChild('邮件营销');
        }

        $emailMarketingItem = $item->getChild('邮件营销');
        if (null === $emailMarketingItem) {
            return;
        }

        $emailMarketingItem
            ->addChild('营销任务')
            ->setUri($this->linkGenerator->getCurdListPage(Task::class))
            ->setAttribute('icon', 'fas fa-tasks')
        ;

        $emailMarketingItem
            ->addChild('邮件模板')
            ->setUri($this->linkGenerator->getCurdListPage(Template::class))
            ->setAttribute('icon', 'fas fa-file-alt')
        ;

        $emailMarketingItem
            ->addChild('发送器')
            ->setUri($this->linkGenerator->getCurdListPage(Sender::class))
            ->setAttribute('icon', 'fas fa-paper-plane')
        ;

        $emailMarketingItem
            ->addChild('收件人')
            ->setUri($this->linkGenerator->getCurdListPage(Receiver::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        $emailMarketingItem
            ->addChild('发送队列')
            ->setUri($this->linkGenerator->getCurdListPage(Queue::class))
            ->setAttribute('icon', 'fas fa-list')
        ;
    }
}
