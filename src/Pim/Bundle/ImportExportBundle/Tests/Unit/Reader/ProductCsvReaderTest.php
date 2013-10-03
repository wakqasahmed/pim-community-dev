<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\ProductCsvReader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvReaderTest extends CsvReaderTest
{
    public function setUp()
    {
        $this->reader = new ProductCsvReader($this->getEntityManagerMock(array('sku', 'name')));
    }

    public function testExtendsCsvReader()
    {
        $this->assertInstanceOf('Pim\Bundle\ImportExportBundle\Reader\CsvReader', $this->reader);
    }

    public function testInitializeUniqueValuesWithAttributeRepository()
    {
        $this->assertAttributeEquals(array('sku' => array(), 'name' => array()), 'uniqueValues', $this->reader);
    }

    public function testReadDuplicateUniqueValue()
    {
        $this->reader->setFilePath(__DIR__ . '/../../fixtures/duplicate_values.csv');

        $stepExecution = $this->getStepExecutionMock();
        $stepExecution
            ->expects($this->once())
            ->method('addReaderWarning')
            ->with(
                $this->reader,
                'The "sku" attribute is unique, the value "SKU-001" was already read in this file.',
                array('sku' => 'SKU-001', 'name' => 'window')
            );

        $this->assertEquals(array('sku' => 'SKU-001', 'name' => 'door'), $this->reader->read($stepExecution));
        $this->assertEquals(array('sku' => 'SKU-002', 'name' => 'hatch'), $this->reader->read($stepExecution));
        $this->assertFalse($this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock($uniqueAttributeCodes)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValue(
                    $this->getEntityRepositoryMock(
                        $uniqueAttributeCodes
                    )
                )
            );

        return $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepositoryMock(array $uniqueAttributeCodes)
    {
        $repository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(array('findUniqueAttributeCodes'))
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('findUniqueAttributeCodes')
            ->will($this->returnValue($uniqueAttributeCodes));

        return $repository;
    }
}
