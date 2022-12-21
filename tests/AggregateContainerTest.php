<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests;

use Moya\DependencyInjection\AggregateContainer;
use Moya\DependencyInjection\Container;
use Moya\DependencyInjection\Definition\DefinitionArray;
use Moya\DependencyInjection\Exception\DuplicateEntryException;
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
}
