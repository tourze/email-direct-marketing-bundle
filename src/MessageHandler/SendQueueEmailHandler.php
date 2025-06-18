<?php

namespace EmailDirectMarketingBundle\MessageHandler;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Tourze\BacktraceHelper\ExceptionPrinter;

/**
 * @see https://symfony.com/doc/current/mailer.html
 * @see https://blog.csdn.net/weixin_43226231/article/details/100011753
 */
#[AsMessageHandler]
class SendQueueEmailHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly QueueRepository $queueRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Transport $transportFactory,
    ) {
    }

    public function __invoke(SendQueueEmailMessage $message)
    {
        $queue = $this->queueRepository->findOneBy([
            'id' => $message->getQueueId(),
            'valid' => true,
        ]);
        if ($queue === null) {
            $this->logger->warning('找不到发送队列', [
                'message' => $message,
            ]);

            return;
        }
        if ($queue->isDone()) {
            $this->logger->warning('任务已完成不重复执行', [
                'queue' => $queue,
            ]);

            return;
        }

        $queue->setSendTime(Carbon::now());

        // 创建服务去真实发送
        $mailer = $this->transportFactory->fromString($queue->getSender()->getDsn());
        $email = (new Email())
            ->from(new Address($queue->getSender()->getEmailAddress(), $queue->getSender()->getSenderName()))
            ->to(new Address($queue->getReceiver()->getEmailAddress(), $queue->getReceiver()->getName()))
            ->subject($queue->getEmailSubject())
            ->html($queue->getEmailBody());

        // 一些必要的header，用于过spam检查的，哎

        try {
            $mailer->send($email);
            $queue->setDone(true);
        } catch (\Throwable $exception) {
            $queue->setDone(false);
            $queue->setErrorMessage(ExceptionPrinter::exception($exception));
        } finally {
            $this->entityManager->persist($queue);
            $this->entityManager->flush();
        }
    }
}
