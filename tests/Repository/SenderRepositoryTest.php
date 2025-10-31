<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Repository\SenderRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(SenderRepository::class)]
#[RunTestsInSeparateProcesses]
final class SenderRepositoryTest extends AbstractRepositoryTestCase
{
    private SenderRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SenderRepository::class);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $this->assertInstanceOf(SenderRepository::class, $this->repository);
    }

    public function testSaveWithFlush(): void
    {
        $sender = $this->createSender();

        $this->repository->save($sender, true);

        $this->assertGreaterThan(0, $sender->getId());
        $saved = $this->repository->find($sender->getId());
        $this->assertInstanceOf(Sender::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $sender = $this->createSender();

        $this->repository->save($sender, false);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->flush();

        $this->assertGreaterThan(0, $sender->getId());
    }

    private function createSender(): Sender
    {
        $sender = new Sender();
        $sender->setTitle('Test Sender');
        $sender->setSenderName('Test Sender');
        $sender->setEmailAddress('sender-' . uniqid() . '@example.com');
        $sender->setDsn('smtp://username:password@smtp.example.com:587');
        $sender->setValid(true);

        return $sender;
    }

    protected function getRepository(): SenderRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createSender();
    }

    public function testCountWithNullValid(): void
    {
        $sender = $this->createSender();
        $sender->setValid(null);
        $this->repository->save($sender);

        $count = $this->repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullValid(): void
    {
        $sender = $this->createSender();
        $sender->setValid(null);
        $this->repository->save($sender);

        $results = $this->repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $uniqueId = uniqid();
        $sender1 = $this->createSender();
        $sender1->setTitle('AAA_OrderBy_Test_' . $uniqueId);
        $this->repository->save($sender1);

        $sender2 = $this->createSender();
        $sender2->setTitle('ZZZ_OrderBy_Test_' . $uniqueId);
        $this->repository->save($sender2);

        $result = $this->repository->findOneBy(['title' => 'AAA_OrderBy_Test_' . $uniqueId], ['title' => 'ASC']);
        $this->assertInstanceOf(Sender::class, $result);
        $this->assertEquals('AAA_OrderBy_Test_' . $uniqueId, $result->getTitle());

        $result = $this->repository->findOneBy(['title' => 'ZZZ_OrderBy_Test_' . $uniqueId], ['title' => 'DESC']);
        $this->assertInstanceOf(Sender::class, $result);
        $this->assertEquals('ZZZ_OrderBy_Test_' . $uniqueId, $result->getTitle());
    }

    public function testFindOneByWithMultipleMatchesShouldRespectOrderBy(): void
    {
        $uniqueId = uniqid();
        $sender1 = $this->createSender();
        $sender1->setTitle('Common_Title_' . $uniqueId);
        $sender1->setSenderName('AAA_Sender');
        $this->repository->save($sender1);

        $sender2 = $this->createSender();
        $sender2->setTitle('Common_Title_' . $uniqueId);
        $sender2->setSenderName('ZZZ_Sender');
        $this->repository->save($sender2);

        $result = $this->repository->findOneBy(['title' => 'Common_Title_' . $uniqueId], ['senderName' => 'ASC']);
        $this->assertInstanceOf(Sender::class, $result);
        $this->assertEquals('AAA_Sender', $result->getSenderName());

        $result = $this->repository->findOneBy(['title' => 'Common_Title_' . $uniqueId], ['senderName' => 'DESC']);
        $this->assertInstanceOf(Sender::class, $result);
        $this->assertEquals('ZZZ_Sender', $result->getSenderName());
    }

    public function testFindOneByWithNullableFieldOrderBy(): void
    {
        $sender1 = $this->createSender();
        $sender1->setTitle('Test_Nullable_Order_1');
        $sender1->setValid(true);
        $this->repository->save($sender1);

        $sender2 = $this->createSender();
        $sender2->setTitle('Test_Nullable_Order_2');
        $sender2->setValid(false);
        $this->repository->save($sender2);

        $result = $this->repository->findOneBy([], ['valid' => 'ASC']);
        $this->assertInstanceOf(Sender::class, $result);

        $result = $this->repository->findOneBy([], ['valid' => 'DESC']);
        $this->assertInstanceOf(Sender::class, $result);
    }

    public function testFindByNullTitleShouldReturnEmptyArray(): void
    {
        $results = $this->repository->findBy(['title' => null]);
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testCountWithNullTitleShouldReturnZero(): void
    {
        $count = $this->repository->count(['title' => null]);
        $this->assertEquals(0, $count);
    }

    public function testFindByNullDsnShouldReturnEmptyArray(): void
    {
        $results = $this->repository->findBy(['dsn' => null]);
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testCountWithNullDsnShouldReturnZero(): void
    {
        $count = $this->repository->count(['dsn' => null]);
        $this->assertEquals(0, $count);
    }

    public function testFindByNullSenderNameShouldReturnEmptyArray(): void
    {
        $results = $this->repository->findBy(['senderName' => null]);
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testCountWithNullSenderNameShouldReturnZero(): void
    {
        $count = $this->repository->count(['senderName' => null]);
        $this->assertEquals(0, $count);
    }

    public function testFindByNullEmailAddressShouldReturnEmptyArray(): void
    {
        $results = $this->repository->findBy(['emailAddress' => null]);
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testCountWithNullEmailAddressShouldReturnZero(): void
    {
        $count = $this->repository->count(['emailAddress' => null]);
        $this->assertEquals(0, $count);
    }

    public function testFindOneByTitleAsNullShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['title' => null]);
        $this->assertNull($result);
    }

    public function testFindOneByDsnAsNullShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['dsn' => null]);
        $this->assertNull($result);
    }

    public function testFindOneBySenderNameAsNullShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['senderName' => null]);
        $this->assertNull($result);
    }

    public function testFindOneByEmailAddressAsNullShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['emailAddress' => null]);
        $this->assertNull($result);
    }
}
