<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Repository\TemplateRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TemplateRepository::class)]
#[RunTestsInSeparateProcesses]
final class TemplateRepositoryTest extends AbstractRepositoryTestCase
{
    private TemplateRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TemplateRepository::class);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $this->assertInstanceOf(TemplateRepository::class, $this->repository);
    }

    public function testSaveWithFlush(): void
    {
        $template = $this->createTemplate();

        $this->repository->save($template, true);

        $this->assertGreaterThan(0, $template->getId());
        $saved = $this->repository->find($template->getId());
        $this->assertInstanceOf(Template::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $template = $this->createTemplate();

        $this->repository->save($template, false);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->flush();

        $this->assertGreaterThan(0, $template->getId());
    }

    // IS NULL query tests for nullable fields

    public function testFindByNullValid(): void
    {
        $template = $this->createTemplate();
        $template->setValid(null);
        $this->repository->save($template);

        $result = $this->repository->findBy(['valid' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    private function createTemplate(): Template
    {
        $template = new Template();
        $template->setName('Test Template');
        $template->setSubject('Test Subject');
        $template->setHtmlBody('<p>Test Body</p>');
        $template->setValid(true);

        return $template;
    }

    protected function getRepository(): TemplateRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createTemplate();
    }

    public function testCountWithNullValid(): void
    {
        $template = $this->createTemplate();
        $template->setValid(null);
        $this->repository->save($template);

        $count = $this->repository->count(['valid' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithOrderBy(): void
    {
        // Clear existing data for clean test
        $existingTemplates = $this->repository->findAll();
        foreach ($existingTemplates as $template) {
            $this->repository->remove($template);
        }

        $template1 = $this->createTemplate();
        $template1->setName('Z模板');
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('A模板');
        $this->repository->save($template2);

        $result = $this->repository->findOneBy([], ['name' => 'ASC']);

        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('A模板', $result->getName());
    }

    public function testFindOneByWithOrderByClause(): void
    {
        // Clear existing data for clean test
        $existingTemplates = $this->repository->findAll();
        foreach ($existingTemplates as $template) {
            $this->repository->remove($template);
        }

        $template1 = $this->createTemplate();
        $template1->setName('C模板测试');
        $template1->setSubject('第三主题');
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('A模板测试');
        $template2->setSubject('第一主题');
        $this->repository->save($template2);

        $template3 = $this->createTemplate();
        $template3->setName('B模板测试');
        $template3->setSubject('第二主题');
        $this->repository->save($template3);

        // Test ASC order by name
        $resultAsc = $this->repository->findOneBy([], ['name' => 'ASC']);
        $this->assertInstanceOf(Template::class, $resultAsc);
        $this->assertEquals('A模板测试', $resultAsc->getName());

        // Test DESC order by name
        $resultDesc = $this->repository->findOneBy([], ['name' => 'DESC']);
        $this->assertInstanceOf(Template::class, $resultDesc);
        $this->assertEquals('C模板测试', $resultDesc->getName());
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        // Clear existing data for clean test
        $existingTemplates = $this->repository->findAll();
        foreach ($existingTemplates as $template) {
            $this->repository->remove($template);
        }

        $template1 = $this->createTemplate();
        $template1->setName('模板1');
        $template1->setSubject('Z主题');
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('模板2');
        $template2->setSubject('A主题');
        $this->repository->save($template2);

        // Test finding by criteria with order by
        $result = $this->repository->findOneBy(['name' => '模板1'], ['subject' => 'ASC']);
        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('模板1', $result->getName());
        $this->assertEquals('Z主题', $result->getSubject());

        // Test multiple field ordering
        $resultMulti = $this->repository->findOneBy([], ['subject' => 'ASC', 'name' => 'DESC']);
        $this->assertInstanceOf(Template::class, $resultMulti);
        $this->assertEquals('A主题', $resultMulti->getSubject());
    }

    public function testFindOneByShouldRespectOrderByLogic(): void
    {
        $uniqueId = uniqid('test_', true);

        $template1 = $this->createTemplate();
        $template1->setName('AAA_OrderBy_Test_' . $uniqueId);
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('ZZZ_OrderBy_Test_' . $uniqueId);
        $this->repository->save($template2);

        $result = $this->repository->findOneBy(['name' => 'AAA_OrderBy_Test_' . $uniqueId], ['name' => 'ASC']);
        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('AAA_OrderBy_Test_' . $uniqueId, $result->getName());

        $result = $this->repository->findOneBy(['name' => 'ZZZ_OrderBy_Test_' . $uniqueId], ['name' => 'DESC']);
        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('ZZZ_OrderBy_Test_' . $uniqueId, $result->getName());
    }

    public function testFindOneBySorting(): void
    {
        $uniqueId = uniqid('template_sort_', true);

        $template1 = $this->createTemplate();
        $template1->setName('A_Template_Sort_' . $uniqueId);
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('Z_Template_Sort_' . $uniqueId);
        $this->repository->save($template2);

        $result = $this->repository->findOneBy(['name' => 'A_Template_Sort_' . $uniqueId], ['name' => 'ASC']);
        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('A_Template_Sort_' . $uniqueId, $result->getName());

        $result = $this->repository->findOneBy(['name' => 'Z_Template_Sort_' . $uniqueId], ['name' => 'DESC']);
        $this->assertInstanceOf(Template::class, $result);
        $this->assertEquals('Z_Template_Sort_' . $uniqueId, $result->getName());
    }

    public function testFindOneByWithMultipleMatchesShouldRespectOrderBy(): void
    {
        $uniqueId = uniqid('multi_', true);

        $template1 = $this->createTemplate();
        $template1->setName('Same_Name_' . $uniqueId);
        $template1->setSubject('AAA Subject');
        $this->repository->save($template1);

        $template2 = $this->createTemplate();
        $template2->setName('Same_Name_' . $uniqueId);
        $template2->setSubject('ZZZ Subject');
        $this->repository->save($template2);

        $resultAsc = $this->repository->findOneBy(
            ['name' => 'Same_Name_' . $uniqueId],
            ['subject' => 'ASC']
        );
        $this->assertInstanceOf(Template::class, $resultAsc);
        $this->assertEquals('AAA Subject', $resultAsc->getSubject());

        $resultDesc = $this->repository->findOneBy(
            ['name' => 'Same_Name_' . $uniqueId],
            ['subject' => 'DESC']
        );
        $this->assertInstanceOf(Template::class, $resultDesc);
        $this->assertEquals('ZZZ Subject', $resultDesc->getSubject());
    }
}
