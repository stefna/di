<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Definition;

use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Definition\PriorityDefinitionArray;
use Stefna\DependencyInjection\Definition\PriorityDefinitionChain;
use Stefna\DependencyInjection\Exception\DuplicateEntryException;
use Stefna\DependencyInjection\Priority;
use PHPUnit\Framework\TestCase;
use Stefna\DependencyInjection\PriorityAware;

final class PriorityDefinitionChainTest extends TestCase
{
	public function testHighPriorityGoesBeforeNormal(): void
	{
		$expectedObj = new \DateTimeImmutable();
		$chain = new PriorityDefinitionChain();
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => $expectedObj,
		]), Priority::High);
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => new \DateTime(),
		]));

		$factory = $chain->getDefinition(\DateTimeInterface::class);
		$this->assertNotNull($factory);
		$obj = $factory(new Container($chain), '');

		$this->assertSame($expectedObj, $obj);
	}

	public function testNormalPriorityGoesBeforeLow(): void
	{
		$expectedObj = new \DateTimeImmutable();
		$chain = new PriorityDefinitionChain();
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => new \DateTime(),
		]), Priority::Low);
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => $expectedObj,
		]));

		$factory = $chain->getDefinition(\DateTimeInterface::class);
		$this->assertNotNull($factory);
		$obj = $factory(new Container($chain), '');

		$this->assertSame($expectedObj, $obj);
	}

	public function testCantAddSameDefinitionTwice(): void
	{
		$definition = new DefinitionArray([
			\DateTimeInterface::class => fn () => new \DateTime(),
		]);

		$this->expectException(DuplicateEntryException::class);

		$chain = new PriorityDefinitionChain();
		$chain->addDefinition($definition, Priority::Low);

		$this->assertTrue($chain->haveDefinition($definition));

		$chain->addDefinition($definition, Priority::High);
	}

	public function testFlattenDefinitionChain(): void
	{
		$expectedObj = new \DateTimeImmutable();
		$chain = new PriorityDefinitionChain();
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => new \DateTime(),
			\ArrayObject::class => fn () => new \ArrayObject(),
		]), Priority::Low);
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => $expectedObj,
		]));

		$defs = $chain->getDefinitions();
		$this->assertCount(2, $defs);

		$factory = $defs[\DateTimeInterface::class];
		$this->assertIsCallable($factory);
		$this->assertSame($expectedObj, $factory());
	}

	public function testNotFound(): void
	{
		$chain = new PriorityDefinitionChain();
		$this->assertNull($chain->getDefinition(\DateTimeInterface::class));
	}

	public function testPriorityAwareDefinitionSource(): void
	{
		$expectedObj = new \DateTimeImmutable();
		$chain = new PriorityDefinitionChain();
		$chain->addDefinition(new DefinitionArray([
			\DateTimeInterface::class => fn () => new \DateTime(),
			\ArrayObject::class => fn () => new \ArrayObject(),
		]));
		$chain->addDefinition(new PriorityDefinitionArray([
			\DateTimeInterface::class => fn () => $expectedObj,
		], Priority::High));

		$defs = $chain->getDefinitions();
		$this->assertCount(2, $defs);

		$factory = $defs[\DateTimeInterface::class];
		$this->assertIsCallable($factory);
		$this->assertSame($expectedObj, $factory());
	}
}
