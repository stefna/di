<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests;

use Stefna\DependencyInjection\AggregateContainer;
use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Exception\DuplicateEntryException;
use PHPUnit\Framework\TestCase;

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
}
