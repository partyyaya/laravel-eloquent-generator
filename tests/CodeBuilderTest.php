<?php

namespace Tests;

use Corp104\Eloquent\Generator\CodeBuilder;
use Corp104\Eloquent\Generator\Resolver;
use Mockery;

class CodeBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnKeyIsOnlyTableNameWithSingleDatabase()
    {
        $schemaGeneratorMock = $this->createSchemaGeneratorMock([], [
            'SomeTable',
        ]);

        /** @var CodeBuilder $target */
        $target = $this->createContainerWithResolverMock([
            'whatever' => $schemaGeneratorMock,
        ])->make(CodeBuilder::class);

        $actual = $target
            ->setNamespace('Whatever')
            ->setConnections(['whatever' => []])
            ->build();

        $this->assertArrayHasKey('/SomeTable.php', $actual);
    }

    /**
     * @test
     */
    public function shouldReturnKeyIsOnlyTableNameWithMultiDatabase()
    {
        $schemaGeneratorMock1 = $this->createSchemaGeneratorMock([], [
            'SomeTable1',
        ]);
        $schemaGeneratorMock2 = $this->createSchemaGeneratorMock([], [
            'SomeTable2',
        ]);

        /** @var CodeBuilder $target */
        $target = $this->createContainerWithResolverMock([
            'SomeConnection1' => $schemaGeneratorMock1,
            'SomeConnection2' => $schemaGeneratorMock2,
        ])->make(CodeBuilder::class);

        $actual = $target
            ->setNamespace('Whatever')
            ->setConnections([
                'SomeConnection1' => [],
                'SomeConnection2' => [],
            ])
            ->build();

        $this->assertArrayHasKey('/SomeConnection1/SomeTable1.php', $actual);
        $this->assertArrayHasKey('/SomeConnection2/SomeTable2.php', $actual);
    }

    private function createContainerWithResolverMock(array $schemaGenerators)
    {
        $resolverMock = Mockery::mock(Resolver::class);
        $resolverMock->shouldReceive('resolveSchemaGenerators')
            ->andReturn($schemaGenerators);
        $resolverMock->shouldReceive('resolveIndexGenerator')
            ->andReturn($this->createIndexGeneratorMock());

        $container = $this->createContainer();
        $container->instance(Resolver::class, $resolverMock);

        return $container;
    }
}
