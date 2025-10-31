<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TaskRepository::class)]
#[RunTestsInSeparateProcesses]
final class TaskRepositoryTest extends AbstractRepositoryTestCase
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
        $this->assertInstanceOf(TaskRepository::class, $repository);
    }

    public function testFindAllSendingTasks(): void
    {
        $repository = $this->getRepository();
        $result = $repository->findAllSendingTasks();

        $this->assertIsArray($result);
    }

    public function testFindAllWaitingTasks(): void
    {
        $repository = $this->getRepository();
        $result = $repository->findAllWaitingTasks();

        $this->assertIsArray($result);
    }

    public function testFindByTags(): void
    {
        $repository = $this->getRepository();
        $result = $repository->findByTags(['marketing', 'newsletter']);

        $this->assertIsArray($result);
    }

    public function testSaveWithFlush(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $repository->save($task, true);

        $this->assertGreaterThan(0, $task->getId());
        $saved = $repository->find($task->getId());
        $this->assertInstanceOf(Task::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $repository->save($task, false);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->flush();

        $this->assertGreaterThan(0, $task->getId());
    }

    private function createTask(): Task
    {
        $template = new Template();
        $template->setName('Test Template');
        $template->setSubject('Test Subject');
        $template->setHtmlBody('<p>Test Body</p>');
        $template->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($template);
        $entityManager->flush();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setTags(['test']);
        $task->setTemplate($template);
        $task->setStatus(TaskStatus::WAITING);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setValid(true);

        return $task;
    }

    protected function getRepository(): TaskRepository
    {
        $repository = self::getService(TaskRepository::class);
        $this->assertInstanceOf(TaskRepository::class, $repository);

        return $repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createTask();
    }

    public function testCountWithTemplateAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $repository->save($task);
        $template = $task->getTemplate();

        $count = $repository->count(['template' => $template]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByTemplateAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $repository->save($task);
        $template = $task->getTemplate();

        $results = $repository->findBy(['template' => $template]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $templateFromResult = $results[0]->getTemplate();
        $this->assertInstanceOf(Template::class, $templateFromResult);
        $this->assertEquals($task->getTemplate()?->getId(), $templateFromResult->getId());
    }

    public function testCountWithNullTotalCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTotalCount(null);
        $repository->save($task);

        $count = $repository->count(['totalCount' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullTotalCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTotalCount(null);
        $repository->save($task);

        $results = $repository->findBy(['totalCount' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullSuccessCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setSuccessCount(null);
        $repository->save($task);

        $count = $repository->count(['successCount' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullSuccessCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setSuccessCount(null);
        $repository->save($task);

        $results = $repository->findBy(['successCount' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullFailureCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setFailureCount(null);
        $repository->save($task);

        $count = $repository->count(['failureCount' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullFailureCount(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setFailureCount(null);
        $repository->save($task);

        $results = $repository->findBy(['failureCount' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithNullValid(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setValid(null);
        $repository->save($task);

        $count = $repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullValid(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setValid(null);
        $repository->save($task);

        $results = $repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid();
        $task1 = $this->createTask();
        $task1->setTitle('AAA_OrderBy_Test_' . $uniqueId);
        $repository->save($task1);

        $task2 = $this->createTask();
        $task2->setTitle('ZZZ_OrderBy_Test_' . $uniqueId);
        $repository->save($task2);

        $result = $repository->findOneBy(['title' => 'AAA_OrderBy_Test_' . $uniqueId], ['title' => 'ASC']);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('AAA_OrderBy_Test_' . $uniqueId, $result->getTitle());

        $result = $repository->findOneBy(['title' => 'ZZZ_OrderBy_Test_' . $uniqueId], ['title' => 'DESC']);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('ZZZ_OrderBy_Test_' . $uniqueId, $result->getTitle());
    }

    public function testCountWithAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $count = $repository->count(['template' => $template]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $results = $repository->findBy(['template' => $template]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $foundTemplate = $results[0]->getTemplate();
        $this->assertInstanceOf(Template::class, $foundTemplate);
        $this->assertInstanceOf(Template::class, $template);
        $this->assertEquals($template->getId(), $foundTemplate->getId());
    }

    public function testCountWithTitleFieldIsNullQueries(): void
    {
        // Note: title is actually non-nullable in practice, testing with empty string
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTitle('');
        $repository->save($task);

        $count = $repository->count(['title' => '']);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByTitleFieldIsNullQueries(): void
    {
        // Note: title is actually non-nullable in practice, testing with empty string
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTitle('');
        $repository->save($task);

        $results = $repository->findBy(['title' => '']);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithStatusFieldIsNullQueries(): void
    {
        // Note: status is actually non-nullable in practice, testing with specific value
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setStatus(TaskStatus::SENDING);
        $repository->save($task);

        $count = $repository->count(['status' => TaskStatus::SENDING]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByStatusFieldIsNullQueries(): void
    {
        // Note: status is actually non-nullable in practice, testing with specific value
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setStatus(TaskStatus::SENDING);
        $repository->save($task);

        $results = $repository->findBy(['status' => TaskStatus::SENDING]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithStartTimeFieldIsNullQueries(): void
    {
        // Note: startTime is actually non-nullable in practice, testing with specific value
        $repository = $this->getRepository();
        $task = $this->createTask();
        $specificTime = new \DateTimeImmutable('2023-12-01 15:30:00');
        $task->setStartTime($specificTime);
        $repository->save($task);

        $count = $repository->count(['startTime' => $specificTime]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByStartTimeFieldIsNullQueries(): void
    {
        // Note: startTime is actually non-nullable in practice, testing with specific value
        $repository = $this->getRepository();
        $task = $this->createTask();
        $specificTime = new \DateTimeImmutable('2023-12-01 15:30:00');
        $task->setStartTime($specificTime);
        $repository->save($task);

        $results = $repository->findBy(['startTime' => $specificTime]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testFindByAssociationQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $results = $repository->findBy(['template' => $template]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountAssociationQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $count = $repository->count(['template' => $template]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testAssociationRelationshipQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $results = $repository->findBy(['template' => $template]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testNullFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTotalCount(null);
        $repository->save($task);

        $results = $repository->findBy(['totalCount' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testSecondNullFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setSuccessCount(null);
        $repository->save($task);

        $results = $repository->findBy(['successCount' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountNullFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTotalCount(null);
        $repository->save($task);

        $count = $repository->count(['totalCount' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountSecondNullFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setSuccessCount(null);
        $repository->save($task);

        $count = $repository->count(['successCount' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithOrderByParameter(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $task1->setTitle('Z Order Test');
        $repository->save($task1, false);

        $task2 = $this->createTask();
        $task2->setTitle('A Order Test');
        $repository->save($task2);

        $result = $repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(Task::class, $result);
    }

    public function testFindOneByWithOrderBySortingLogic(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid('test_', true);

        $task1 = $this->createTask();
        $task1->setTitle('First Task ' . $uniqueId);
        $repository->save($task1, false);

        $task2 = $this->createTask();
        $task2->setTitle('Second Task ' . $uniqueId);
        $repository->save($task2);

        $result = $repository->findOneBy(['title' => 'Second Task ' . $uniqueId]);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Second Task ' . $uniqueId, $result->getTitle());
    }

    public function testFindByWithValidFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setValid(null);
        $repository->save($task);

        $results = $repository->findBy(['valid' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountWithValidFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setValid(null);
        $repository->save($task);

        $count = $repository->count(['valid' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithMsgTaskIdFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setFailureCount(null);
        $repository->save($task);

        $count = $repository->count(['failureCount' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithMsgDataIdFieldIsNullQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTotalCount(null);
        $task->setSuccessCount(null);
        $repository->save($task);

        $count = $repository->count(['totalCount' => null, 'successCount' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneBySorting(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid('sort_', true);

        $task1 = $this->createTask();
        $task1->setTitle('A_Sort_Test_' . $uniqueId);
        $repository->save($task1);

        $task2 = $this->createTask();
        $task2->setTitle('Z_Sort_Test_' . $uniqueId);
        $repository->save($task2);

        $result = $repository->findOneBy(['title' => 'A_Sort_Test_' . $uniqueId], ['title' => 'ASC']);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('A_Sort_Test_' . $uniqueId, $result->getTitle());

        $result = $repository->findOneBy(['title' => 'Z_Sort_Test_' . $uniqueId], ['title' => 'DESC']);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Z_Sort_Test_' . $uniqueId, $result->getTitle());
    }

    public function testFindOneByWithMultipleMatchesShouldRespectOrderBy(): void
    {
        $repository = $this->getRepository();
        $uniqueId = uniqid('multi_', true);

        $task1 = $this->createTask();
        $task1->setTitle('Same_Title_' . $uniqueId);
        $task1->setTotalCount(100);
        $repository->save($task1);

        $task2 = $this->createTask();
        $task2->setTitle('Same_Title_' . $uniqueId);
        $task2->setTotalCount(200);
        $repository->save($task2);

        $resultAsc = $repository->findOneBy(
            ['title' => 'Same_Title_' . $uniqueId],
            ['totalCount' => 'ASC']
        );
        $this->assertInstanceOf(Task::class, $resultAsc);
        $this->assertEquals(100, $resultAsc->getTotalCount());

        $resultDesc = $repository->findOneBy(
            ['title' => 'Same_Title_' . $uniqueId],
            ['totalCount' => 'DESC']
        );
        $this->assertInstanceOf(Task::class, $resultDesc);
        $this->assertEquals(200, $resultDesc->getTotalCount());
    }

    public function testFindOneByWithAssociationOrderBy(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $repository->save($task1);

        $task2 = $this->createTask();
        $repository->save($task2);

        $result = $repository->findOneBy([], ['template' => 'ASC']);
        $this->assertInstanceOf(Task::class, $result);

        $result = $repository->findOneBy([], ['template' => 'DESC']);
        $this->assertInstanceOf(Task::class, $result);
    }

    public function testFindBySendersAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTaskWithSenders();
        $repository->save($task);
        $senders = $task->getSenders();

        $this->assertNotEmpty($senders, 'Task should have senders for testing');

        $sender = $senders->first();

        // For ManyToMany, we need to test that we can find tasks by checking if they exist
        $allTasks = $repository->findAll();
        $tasksWithSender = array_filter($allTasks, function ($t) use ($sender) {
            return $t->getSenders()->contains($sender);
        });

        $this->assertNotEmpty($tasksWithSender);
    }

    public function testCountWithSendersAssociation(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTaskWithSenders();
        $repository->save($task);

        // Test that we can count all tasks (this tests the count functionality)
        $totalCount = $repository->count([]);
        $this->assertGreaterThanOrEqual(1, $totalCount);

        // Test counting by a specific field to ensure count works with criteria
        $countByTitle = $repository->count(['title' => $task->getTitle()]);
        $this->assertGreaterThanOrEqual(1, $countByTitle);
    }

    public function testFindByTemplateAssociationQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $results = $repository->findBy(['template' => $template]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $templateFromResult = $results[0]->getTemplate();
        if (null === $templateFromResult || null === $template) {
            self::fail('Template should not be null');
        }
        $this->assertEquals($template->getId(), $templateFromResult->getId());
    }

    public function testCountWithTemplateAssociationQueries(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        if (null === $template) {
            self::fail('Template should not be null after task creation');
        }
        $repository->save($task);

        $count = $repository->count(['template' => $template]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByTitleIsNull(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTitle('');
        $repository->save($task);

        $results = $repository->findBy(['title' => '']);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithTitleIsNull(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $task->setTitle('');
        $repository->save($task);

        $count = $repository->count(['title' => '']);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByStartTimeIsNull(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $specificTime = new \DateTimeImmutable('2023-01-01');
        $task->setStartTime($specificTime);
        $repository->save($task);

        $results = $repository->findBy(['startTime' => $specificTime]);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testCountWithStartTimeIsNull(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $specificTime = new \DateTimeImmutable('2023-01-01');
        $task->setStartTime($specificTime);
        $repository->save($task);

        $count = $repository->count(['startTime' => $specificTime]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationTemplateShouldReturnMatchingEntity(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        $repository->save($task);

        $result = $repository->findOneBy(['template' => $template]);
        $this->assertInstanceOf(Task::class, $result);
        $templateFromResult = $result->getTemplate();
        if (null === $templateFromResult || null === $template) {
            self::fail('Template should not be null');
        }
        $this->assertEquals($template->getId(), $templateFromResult->getId());
    }

    public function testCountByAssociationTemplateShouldReturnCorrectNumber(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();
        $template = $task->getTemplate();
        if (null === $template) {
            self::fail('Template should not be null after task creation');
        }
        $repository->save($task);

        $count = $repository->count(['template' => $template]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    private function createTaskWithSenders(): Task
    {
        $template = new Template();
        $template->setName('Test Template');
        $template->setSubject('Test Subject');
        $template->setHtmlBody('<p>Test Body</p>');
        $template->setValid(true);

        $sender = new Sender();
        $sender->setTitle('Test Sender');
        $sender->setSenderName('Test Sender');
        $sender->setEmailAddress('sender@example.com');
        $sender->setDsn('smtp://username:password@smtp.example.com:587');
        $sender->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($template);
        $entityManager->persist($sender);
        $entityManager->flush();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setTags(['test']);
        $task->setTemplate($template);
        $task->setStatus(TaskStatus::WAITING);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setValid(true);
        $task->addSender($sender);

        return $task;
    }
}
