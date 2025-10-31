<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Queue;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\QueueRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(QueueRepository::class)]
#[RunTestsInSeparateProcesses]
final class QueueRepositoryTest extends AbstractRepositoryTestCase
{
    private QueueRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(QueueRepository::class);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $this->assertInstanceOf(QueueRepository::class, $this->repository);
    }

    public function testSaveWithFlush(): void
    {
        $queue = $this->createQueue();

        $this->repository->save($queue, true);

        $this->assertGreaterThan(0, $queue->getId());
        $saved = $this->repository->find($queue->getId());
        $this->assertInstanceOf(Queue::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $queue = $this->createQueue();

        $this->repository->save($queue, false);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->flush();

        $this->assertGreaterThan(0, $queue->getId());
    }

    public function testFindNullableFields(): void
    {
        $queue = $this->createQueue();
        $queue->setSendTime(null);
        $queue->setDone(null);
        $queue->setErrorMessage(null);
        $queue->setValid(null);
        $this->repository->save($queue);

        $result = $this->repository->findBy(['sendTime' => null]);
        $this->assertNotEmpty($result);

        $result = $this->repository->findBy(['done' => null]);
        $this->assertNotEmpty($result);

        $result = $this->repository->findBy(['errorMessage' => null]);
        $this->assertNotEmpty($result);
    }

    private function createQueue(): Queue
    {
        $template = new Template();
        $template->setName('Test Template');
        $template->setSubject('Test Subject');
        $template->setHtmlBody('<p>Test Body</p>');
        $template->setValid(true);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setTags(['test']);
        $task->setTemplate($template);
        $task->setStatus(TaskStatus::WAITING);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setValid(true);

        $receiver = new Receiver();
        $receiver->setEmailAddress('test@example.com');
        $receiver->setName('Test User');

        $sender = new Sender();
        $sender->setTitle('Test Sender');
        $sender->setSenderName('Test Sender');
        $sender->setEmailAddress('sender@example.com');
        $sender->setDsn('smtp://username:password@smtp.example.com:587');
        $sender->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($template);
        $entityManager->persist($task);
        $entityManager->persist($receiver);
        $entityManager->persist($sender);
        $entityManager->flush();

        $queue = new Queue();
        $queue->setTask($task);
        $queue->setReceiver($receiver);
        $queue->setSender($sender);
        $queue->setEmailSubject('Test Subject');
        $queue->setEmailBody('Test Body');

        return $queue;
    }

    protected function getRepository(): QueueRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createQueue();
    }

    public function testCountWithTaskAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $taskId = $queue->getTask()?->getId();
        if (null === $taskId) {
            self::fail('Task ID should not be null after saving');
        }

        $count = $this->repository->count(['task' => $taskId]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByTaskAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $task = $queue->getTask();

        $results = $this->repository->findBy(['task' => $task]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $taskFromResult = $results[0]->getTask();
        if (null === $taskFromResult || null === $task) {
            self::fail('Task should not be null');
        }
        $this->assertEquals($task->getId(), $taskFromResult->getId());
    }

    public function testFindByReceiverAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $receiver = $queue->getReceiver();

        $results = $this->repository->findBy(['receiver' => $receiver]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $receiverFromResult = $results[0]->getReceiver();
        if (null === $receiverFromResult || null === $receiver) {
            self::fail('Receiver should not be null');
        }
        $this->assertEquals($receiver->getId(), $receiverFromResult->getId());
    }

    public function testCountWithNullSendTime(): void
    {
        $queue = $this->createQueue();
        $queue->setSendTime(null);
        $this->repository->save($queue);

        $count = $this->repository->count(['sendTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullErrorMessage(): void
    {
        $queue = $this->createQueue();
        $queue->setErrorMessage(null);
        $this->repository->save($queue);

        $count = $this->repository->count(['errorMessage' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindBySenderAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $sender = $queue->getSender();

        $results = $this->repository->findBy(['sender' => $sender]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $senderFromResult = $results[0]->getSender();
        if (null === $senderFromResult || null === $sender) {
            self::fail('Sender should not be null');
        }
        $this->assertEquals($sender->getId(), $senderFromResult->getId());
    }

    public function testCountWithSenderAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $senderId = $queue->getSender()?->getId();
        if (null === $senderId) {
            self::fail('Sender ID should not be null after saving');
        }

        $count = $this->repository->count(['sender' => $senderId]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithReceiverAssociation(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $receiverId = $queue->getReceiver()?->getId();
        if (null === $receiverId) {
            self::fail('Receiver ID should not be null after saving');
        }

        $count = $this->repository->count(['receiver' => $receiverId]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullSendTime(): void
    {
        $queue = $this->createQueue();
        $queue->setSendTime(null);
        $this->repository->save($queue);

        $results = $this->repository->findBy(['sendTime' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindByNullErrorMessage(): void
    {
        $queue = $this->createQueue();
        $queue->setErrorMessage(null);
        $this->repository->save($queue);

        $results = $this->repository->findBy(['errorMessage' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullDone(): void
    {
        $queue = $this->createQueue();
        $queue->setDone(null);
        $this->repository->save($queue);

        $count = $this->repository->count(['done' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullDone(): void
    {
        $queue = $this->createQueue();
        $queue->setDone(null);
        $this->repository->save($queue);

        $results = $this->repository->findBy(['done' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullValid(): void
    {
        $queue = $this->createQueue();
        $queue->setValid(null);
        $this->repository->save($queue);

        $count = $this->repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullValid(): void
    {
        $queue = $this->createQueue();
        $queue->setValid(null);
        $this->repository->save($queue);

        $results = $this->repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $uniqueId = uniqid();
        $queue1 = $this->createQueue();
        $queue1->setEmailSubject('AAA_OrderBy_Test_' . $uniqueId);
        $this->repository->save($queue1);

        $queue2 = $this->createQueue();
        $queue2->setEmailSubject('ZZZ_OrderBy_Test_' . $uniqueId);
        $this->repository->save($queue2);

        $result = $this->repository->findOneBy(['emailSubject' => 'AAA_OrderBy_Test_' . $uniqueId], ['emailSubject' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);
        $this->assertEquals('AAA_OrderBy_Test_' . $uniqueId, $result->getEmailSubject());

        $result = $this->repository->findOneBy(['emailSubject' => 'ZZZ_OrderBy_Test_' . $uniqueId], ['emailSubject' => 'DESC']);
        $this->assertInstanceOf(Queue::class, $result);
        $this->assertEquals('ZZZ_OrderBy_Test_' . $uniqueId, $result->getEmailSubject());
    }

    public function testFindOneByWithMultipleMatchesShouldRespectOrderBy(): void
    {
        $uniqueId = uniqid();

        $queue1 = $this->createQueue();
        $queue1->setEmailSubject('Same_Subject_' . $uniqueId);
        $queue1->setEmailBody('AAAA');
        $this->repository->save($queue1);

        $queue2 = $this->createQueue();
        $queue2->setEmailSubject('Same_Subject_' . $uniqueId);
        $queue2->setEmailBody('ZZZZ');
        $this->repository->save($queue2);

        $resultAsc = $this->repository->findOneBy(
            ['emailSubject' => 'Same_Subject_' . $uniqueId],
            ['emailBody' => 'ASC']
        );
        $this->assertInstanceOf(Queue::class, $resultAsc);
        $this->assertEquals('AAAA', $resultAsc->getEmailBody());

        $resultDesc = $this->repository->findOneBy(
            ['emailSubject' => 'Same_Subject_' . $uniqueId],
            ['emailBody' => 'DESC']
        );
        $this->assertInstanceOf(Queue::class, $resultDesc);
        $this->assertEquals('ZZZZ', $resultDesc->getEmailBody());
    }

    public function testFindOneByWithAssociationOrderBy(): void
    {
        $queue1 = $this->createQueue();
        $queue1->setEmailSubject('Association_Test_1');
        $this->repository->save($queue1);

        $queue2 = $this->createQueue();
        $queue2->setEmailSubject('Association_Test_2');
        $this->repository->save($queue2);

        $result = $this->repository->findOneBy([], ['task' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);

        $result = $this->repository->findOneBy([], ['receiver' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);

        $result = $this->repository->findOneBy([], ['sender' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testFindOneByWithNullableFieldOrderBy(): void
    {
        $queue1 = $this->createQueue();
        $queue1->setSendTime(new \DateTimeImmutable('2023-01-01'));
        $queue1->setDone(true);
        $queue1->setErrorMessage('Error A');
        $queue1->setValid(true);
        $this->repository->save($queue1);

        $queue2 = $this->createQueue();
        $queue2->setSendTime(new \DateTimeImmutable('2023-01-02'));
        $queue2->setDone(false);
        $queue2->setErrorMessage('Error B');
        $queue2->setValid(false);
        $this->repository->save($queue2);

        $result = $this->repository->findOneBy([], ['sendTime' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);

        $result = $this->repository->findOneBy([], ['done' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);

        $result = $this->repository->findOneBy([], ['errorMessage' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);

        $result = $this->repository->findOneBy([], ['valid' => 'ASC']);
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testFindOneByAssociationTaskShouldReturnMatchingEntity(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $task = $queue->getTask();

        $result = $this->repository->findOneBy(['task' => $task]);
        $this->assertInstanceOf(Queue::class, $result);
        $taskFromResult = $result->getTask();
        if (null === $taskFromResult || null === $task) {
            self::fail('Task should not be null');
        }
        $this->assertEquals($task->getId(), $taskFromResult->getId());
    }

    public function testFindOneByAssociationReceiverShouldReturnMatchingEntity(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $receiver = $queue->getReceiver();

        $result = $this->repository->findOneBy(['receiver' => $receiver]);
        $this->assertInstanceOf(Queue::class, $result);
        $receiverFromResult = $result->getReceiver();
        if (null === $receiverFromResult || null === $receiver) {
            self::fail('Receiver should not be null');
        }
        $this->assertEquals($receiver->getId(), $receiverFromResult->getId());
    }

    public function testFindOneByAssociationSenderShouldReturnMatchingEntity(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $sender = $queue->getSender();

        $result = $this->repository->findOneBy(['sender' => $sender]);
        $this->assertInstanceOf(Queue::class, $result);
        $senderFromResult = $result->getSender();
        if (null === $senderFromResult || null === $sender) {
            self::fail('Sender should not be null');
        }
        $this->assertEquals($sender->getId(), $senderFromResult->getId());
    }

    public function testCountByAssociationTaskShouldReturnCorrectNumber(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $task = $queue->getTask();

        $count = $this->repository->count(['task' => $task]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationReceiverShouldReturnCorrectNumber(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $receiver = $queue->getReceiver();

        $count = $this->repository->count(['receiver' => $receiver]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationSenderShouldReturnCorrectNumber(): void
    {
        $queue = $this->createQueue();
        $this->repository->save($queue);
        $sender = $queue->getSender();

        $count = $this->repository->count(['sender' => $sender]);
        $this->assertGreaterThanOrEqual(1, $count);
    }
}
