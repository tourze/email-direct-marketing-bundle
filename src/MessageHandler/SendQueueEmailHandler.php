<?php

namespace EmailDirectMarketingBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use Monolog\Attribute\WithMonologChannel;
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
#[WithMonologChannel(channel: 'email_direct_marketing')]
class SendQueueEmailHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly QueueRepository $queueRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Transport $transportFactory,
    ) {
    }

    public function __invoke(SendQueueEmailMessage $message): void
    {
        $queue = $this->queueRepository->findOneBy([
            'id' => $message->getQueueId(),
            'valid' => true,
        ]);
        if (null === $queue) {
            $this->logger->warning('找不到发送队列', [
                'message' => $message,
            ]);

            return;
        }
        if (true === $queue->isDone()) {
            $this->logger->warning('任务已完成不重复执行', [
                'queue' => $queue,
            ]);

            return;
        }

        $queue->setSendTime(new \DateTimeImmutable());

        // 创建服务去真实发送
        $sender = $queue->getSender();
        $receiver = $queue->getReceiver();

        if (null === $sender || null === $receiver) {
            $this->logger->warning('发送队列缺少发送者或接收者信息', [
                'queue_id' => $queue->getId(),
            ]);

            return;
        }

        $senderDsn = $sender->getDsn();
        $senderEmail = $sender->getEmailAddress();
        $senderName = $sender->getSenderName();
        $receiverEmail = $receiver->getEmailAddress();
        $receiverName = $receiver->getName();
        $emailSubject = $queue->getEmailSubject();

        if (null === $senderDsn || null === $senderEmail || null === $receiverEmail || null === $emailSubject) {
            $this->logger->warning('发送队列信息不完整', [
                'queue_id' => $queue->getId(),
            ]);

            return;
        }

        $mailer = $this->transportFactory->fromString($senderDsn);
        $email = (new Email())
            ->from(new Address($senderEmail, $senderName ?? ''))
            ->to(new Address($receiverEmail, $receiverName ?? ''))
            ->subject($emailSubject)
            ->html($queue->getEmailBody())
        ;

        // 一些必要的header，用于过spam检查的，哎

        try {
            $mailer->send($email);
            $queue->setDone(true);
            $this->logger->info('邮件发送成功', [
                'queue_id' => $queue->getId(),
            ]);
        } catch (\Throwable $exception) {
            $queue->setDone(false);
            $queue->setErrorMessage(ExceptionPrinter::exception($exception));
        } finally {
            $this->entityManager->persist($queue);
            $this->entityManager->flush();
        }
    }
}
