<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests;

use Moya\DependencyInjection\AggregateContainer;
use Moya\DependencyInjection\Container;
use Moya\DependencyInjection\ContainerBuilder;
use Moya\DependencyInjection\Definition\DefinitionArray;
use Moya\DependencyInjection\Priority;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

final class ContainerBuilderTest extends TestCase
{
	public function testSimpleDefinitionSimpleContainer(): void
	{
		$builder = new ContainerBuilder();
		$builder->addDefinition([
			\DateTimeInterface::class => fn () => new \DateTimeImmutable(),
		]);

		$container = $builder->build();

		$this->assertInstanceOf(Container::class, $container);

		$this->assertTrue($container->has(\DateTimeInterface::class));
	}

	public function testMixOfDefinitionAndContainers(): void
	{
		$builder = new ContainerBuilder();
		$builder->addContainer(new Container(new DefinitionArray([\DateTimeInterface::class => fn() => new \DateTimeImmutable()])));
		$builder->addDefinition([
			'test' => fn () => 'test',
		]);

		$container = $builder->build();

		$this->assertInstanceOf(AggregateContainer::class, $container);

		$this->assertTrue($container->has(\DateTimeInterface::class));
		$this->assertTrue($container->has('test'));
	}

	public function testContainerPriority(): void
	{
		$builder = new ContainerBuilder();
		$builder->addContainer(new Container(new DefinitionArray([
			'test1' => fn() => 'test1',
			'test2' => fn() => 'test2',
		])), Priority::Low);
		$builder->addDefinition(__DIR__ . '/Definition/resources/overload-definition.php');

		$container = $builder->build();

		$this->assertInstanceOf(AggregateContainer::class, $container);

		$this->assertSame('test1.1', $container->get('test1'));
		$this->assertSame('test2', $container->get('test2'));
	}

	public function testContainerReturnsSameInstanceOnMultipleCalls(): void
	{
		$builder = new ContainerBuilder();
		$builder->addContainer(new Container(new DefinitionArray([\DateTimeInterface::class => fn() => new \DateTimeImmutable()])));
		$builder->addDefinition([
			'test' => fn () => new \ArrayObject(),
		]);

		$container = $builder->build();

		$this->assertInstanceOf(AggregateContainer::class, $container);

		$v1 = $container->get(\DateTimeInterface::class);
		$v2 = $container->get(\DateTimeInterface::class);
		$this->assertSame($v1, $v2);

		$v3 = $container->get('test');
		$v4 = $container->get('test');
		$this->assertSame($v3, $v4);
	}

	public function testNotFound(): void
	{
		$builder = new ContainerBuilder();
		$builder->addContainer(new Container(new DefinitionArray([\DateTimeInterface::class => fn() => new \DateTimeImmutable()])));
		$builder->addDefinition([
			'test' => fn () => new \ArrayObject(),
		]);

		$container = $builder->build();

		$this->assertInstanceOf(AggregateContainer::class, $container);

		$this->expectException(NotFoundExceptionInterface::class);

		$this->assertFalse($container->has('not-found'));
		$container->get('not-found-2');
	}
}
