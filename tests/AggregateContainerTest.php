<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\AggregateContainer;
use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Exception\DuplicateEntryException;
use PHPUnit\Framework\TestCase;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithArgs;

final class AggregateContainerTest extends TestCase
{
	public function testHaveContainer(): void
	{
		$aggregateContainer = new AggregateContainer();
		$container = new Container(new DefinitionArray([]));
		$aggregateContainer->addContainer($container);

		$this->assertTrue($aggregateContainer->haveContainer($container));
	}

	public function testCantAddSameContainerTwice(): void
	{
		$this->expectException(DuplicateEntryException::class);

		$aggregateContainer = new AggregateContainer();
		$container = new Container(new DefinitionArray([]));
		$aggregateContainer->addContainer($container);
		$aggregateContainer->addContainer($container);
	}

	public function testFastPathHasCheck(): void
	{
		$aggregateContainer = new AggregateContainer();
		$container = new Container(new DefinitionArray([
			\DateTimeImmutable::class => fn () => new \DateTimeImmutable(),
		]));
		$aggregateContainer->addContainer($container);

		$aggregateContainer->has(\DateTimeImmutable::class);

		$this->assertInstanceOf(\DateTimeImmutable::class, $aggregateContainer->get(\DateTimeImmutable::class));

		$this->assertTrue($aggregateContainer->has(\DateTimeImmutable::class));
	}

	public function testAggregateContainerGetUsedAsRootContainerWhenCallingFactories(): void
	{
		$time = new \DateTimeImmutable();
		$aggregateContainer = new AggregateContainer();
		$container1 = new Container(new DefinitionArray([
			\DateTimeImmutable::class => fn () => $time,
		]));
		$container2 = new Container(new DefinitionArray([
			TestWithArgs::class => function (ContainerInterface $c) use ($aggregateContainer) {
				$this->assertSame($aggregateContainer, $c);
				return new TestWithArgs($c->get(\DateTimeImmutable::class));
			},
		]));
		$aggregateContainer->addContainer($container1);
		$aggregateContainer->addContainer($container2);

		$entity = $aggregateContainer->get(TestWithArgs::class);
		$this->assertSame($time, $entity->date);
	}

	public function testNestedAggregateContainerGetUsedAsRootContainerWhenCallingFactories(): void
	{
		$time = new \DateTimeImmutable();
		$rootAggregateContainer = new AggregateContainer();
		$aggregateContainer1 = new AggregateContainer();
		$aggregateContainer2 = new AggregateContainer();
		$rootAggregateContainer->addContainer($aggregateContainer1);
		$rootAggregateContainer->addContainer($aggregateContainer2);

		$aggregateContainer1->addContainer(new Container(new DefinitionArray([
			\DateTimeImmutable::class => fn () => $time,
		])));
		$aggregateContainer2->addContainer(new Container(new DefinitionArray([
			TestWithArgs::class => function (ContainerInterface $c) use ($rootAggregateContainer) {
				$this->assertSame($rootAggregateContainer, $c);
				return new TestWithArgs($c->get(\DateTimeImmutable::class));
			},
		])));

		$entity = $rootAggregateContainer->get(TestWithArgs::class);
		$this->assertSame($time, $entity->date);
	}

	public function testNested2AggregateContainerGetUsedAsRootContainerWhenCallingFactories(): void
	{
		$time = new \DateTimeImmutable();
		$rootAggregateContainer = new AggregateContainer('1');
		$aggregateContainer1 = new AggregateContainer('2');
		$aggregateContainer2 = new AggregateContainer('3');

		$aggregateContainer1->addContainer(new Container(new DefinitionArray([
			\DateTimeImmutable::class => fn () => $time,
		])));
		$aggregateContainer2->addContainer(new Container(new DefinitionArray([
			TestWithArgs::class => function (ContainerInterface $c) use ($rootAggregateContainer) {
				$c;

				$this->assertSame($rootAggregateContainer, $c);
				return new TestWithArgs($c->get(\DateTimeImmutable::class));
			},
		])));

		$rootAggregateContainer->addContainer($aggregateContainer2);
		$rootAggregateContainer->addContainer($aggregateContainer1);

		$entity = $rootAggregateContainer->get(TestWithArgs::class);
		$this->assertSame($time, $entity->date);
	}
}
