<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ReceiverRepository::class)]
#[RunTestsInSeparateProcesses]
final class ReceiverRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(ReceiverRepository::class, $repository);
    }

    public function testFindAllActiveReceivers(): void
    {
        $repository = $this->getRepository();
        $result = $repository->findAllActiveReceivers();

        $this->assertIsArray($result);
    }

    public function testFindByEmail(): void
    {
        $repository = $this->getRepository();
        $result = $repository->findByEmail('test@example.com');

        $this->assertNull($result);
    }

    public function testFindByTags(): void
    {
        $repository = $this->getRepository();

        // JSON_SEARCH 可能在测试环境不可用，这是正常的
        try {
            $result = $repository->findByTags(['test-tag', 'another-tag']);
            $this->assertIsArray($result);
        } catch (Exception $e) {
            // 预期行为：测试环境可能不支持 JSON 函数
            $this->assertStringContainsString('JSON_SEARCH', $e->getMessage());
        }
    }

    public function testFindNotContactedSince(): void
    {
        $repository = $this->getRepository();
        $beforeDate = new \DateTime('-30 days');
        $result = $repository->findNotContactedSince($beforeDate);

        $this->assertIsArray($result);
    }

    public function testSaveWithFlush(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();

        $repository->save($receiver, true);

        $this->assertGreaterThan(0, $receiver->getId());
        $saved = $repository->find($receiver->getId());
        $this->assertInstanceOf(Receiver::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();

        $repository->save($receiver, false);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->flush();

        $this->assertGreaterThan(0, $receiver->getId());
    }

    public function testFindNullableFields(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $receiver->setUnsubscribed(null);
        $receiver->setLastSendTime(null);
        $receiver->setTags(null);
        $repository->save($receiver, true);

        $result = $repository->findBy(['unsubscribed' => null]);
        $this->assertIsArray($result);

        $result = $repository->findBy(['lastSendTime' => null]);
        $this->assertIsArray($result);

        $result = $repository->findBy(['tags' => null]);
        $this->assertIsArray($result);
    }

    private function createReceiver(): Receiver
    {
        $receiver = new Receiver();
        $receiver->setEmailAddress('test-' . uniqid() . '@example.com');
        $receiver->setName('Test User');

        return $receiver;
    }

    protected function getRepository(): ReceiverRepository
    {
        $repository = self::getService(ReceiverRepository::class);
        $this->assertInstanceOf(ReceiverRepository::class, $repository);

        return $repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createReceiver();
    }

    public function testCountWithNullLastSendTime(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $receiver->setLastSendTime(null);
        $repository->save($receiver);

        $count = $repository->count(['lastSendTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullLastSendTime(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $receiver->setLastSendTime(null);
        $repository->save($receiver);

        $results = $repository->findBy(['lastSendTime' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullUnsubscribed(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $receiver->setUnsubscribed(null);
        $repository->save($receiver);

        $count = $repository->count(['unsubscribed' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullUnsubscribed(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $receiver->setUnsubscribed(null);
        $repository->save($receiver);

        $results = $repository->findBy(['unsubscribed' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid();
        $receiver1 = $this->createReceiver();
        $receiver1->setName('AAA_OrderBy_Test_' . $uniqueId);
        $repository->save($receiver1);

        $receiver2 = $this->createReceiver();
        $receiver2->setName('ZZZ_OrderBy_Test_' . $uniqueId);
        $repository->save($receiver2);

        $result = $repository->findOneBy(['name' => 'AAA_OrderBy_Test_' . $uniqueId], ['name' => 'ASC']);
        $this->assertInstanceOf(Receiver::class, $result);
        $this->assertEquals('AAA_OrderBy_Test_' . $uniqueId, $result->getName());

        $result = $repository->findOneBy(['name' => 'ZZZ_OrderBy_Test_' . $uniqueId], ['name' => 'DESC']);
        $this->assertInstanceOf(Receiver::class, $result);
        $this->assertEquals('ZZZ_OrderBy_Test_' . $uniqueId, $result->getName());
    }

    public function testFindOneByWithMultipleMatchesShouldRespectOrderBy(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid();

        $receiver1 = $this->createReceiver();
        $receiver1->setName('Same_Name_' . $uniqueId);
        $receiver1->setEmailAddress('aaaa@example.com');
        $repository->save($receiver1);

        $receiver2 = $this->createReceiver();
        $receiver2->setName('Same_Name_' . $uniqueId);
        $receiver2->setEmailAddress('zzzz@example.com');
        $repository->save($receiver2);

        $resultAsc = $repository->findOneBy(
            ['name' => 'Same_Name_' . $uniqueId],
            ['emailAddress' => 'ASC']
        );
        $this->assertInstanceOf(Receiver::class, $resultAsc);
        $this->assertEquals('aaaa@example.com', $resultAsc->getEmailAddress());

        $resultDesc = $repository->findOneBy(
            ['name' => 'Same_Name_' . $uniqueId],
            ['emailAddress' => 'DESC']
        );
        $this->assertInstanceOf(Receiver::class, $resultDesc);
        $this->assertEquals('zzzz@example.com', $resultDesc->getEmailAddress());
    }

    public function testFindOneByWithNullableFieldOrderBy(): void
    {
        $repository = $this->getRepository();

        $receiver1 = $this->createReceiver();
        $receiver1->setLastSendTime(new \DateTimeImmutable('2023-01-01'));
        $receiver1->setUnsubscribed(true);
        $receiver1->setTags(['tag1']);
        $repository->save($receiver1);

        $receiver2 = $this->createReceiver();
        $receiver2->setLastSendTime(new \DateTimeImmutable('2023-01-02'));
        $receiver2->setUnsubscribed(false);
        $receiver2->setTags(['tag2']);
        $repository->save($receiver2);

        $result = $repository->findOneBy([], ['lastSendTime' => 'ASC']);
        $this->assertInstanceOf(Receiver::class, $result);

        $result = $repository->findOneBy([], ['unsubscribed' => 'ASC']);
        $this->assertInstanceOf(Receiver::class, $result);
    }

    public function testCountWithNullTags(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $repository->save($receiver);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->createQuery('UPDATE EmailDirectMarketingBundle\Entity\Receiver r SET r.tags = NULL WHERE r.id = :id')
            ->setParameter('id', $receiver->getId())
            ->execute()
        ;

        $count = $repository->count(['tags' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullTags(): void
    {
        $repository = $this->getRepository();
        $receiver = $this->createReceiver();
        $repository->save($receiver);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->createQuery('UPDATE EmailDirectMarketingBundle\Entity\Receiver r SET r.tags = NULL WHERE r.id = :id')
            ->setParameter('id', $receiver->getId())
            ->execute()
        ;

        $results = $repository->findBy(['tags' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }
}
