<?php

namespace EmailDirectMarketingBundle\Service;

use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('邮件营销') === null) {
            $item->addChild('邮件营销');
        }

        $item->getChild('邮件营销')
            ->addChild('营销任务')
            ->setUri($this->linkGenerator->getCurdListPage(Task::class))
            ->setAttribute('icon', 'fas fa-tasks');

        $item->getChild('邮件营销')
            ->addChild('邮件模板')
            ->setUri($this->linkGenerator->getCurdListPage(Template::class))
            ->setAttribute('icon', 'fas fa-file-alt');

        $item->getChild('邮件营销')
            ->addChild('发送器')
            ->setUri($this->linkGenerator->getCurdListPage(Sender::class))
            ->setAttribute('icon', 'fas fa-paper-plane');

        $item->getChild('邮件营销')
            ->addChild('收件人')
            ->setUri($this->linkGenerator->getCurdListPage(Receiver::class))
            ->setAttribute('icon', 'fas fa-users');

        $item->getChild('邮件营销')
            ->addChild('发送队列')
            ->setUri($this->linkGenerator->getCurdListPage(Queue::class))
            ->setAttribute('icon', 'fas fa-list');
    }
}
