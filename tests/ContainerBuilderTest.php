<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests;

use Stefna\DependencyInjection\AggregateContainer;
use Stefna\DependencyInjection\Attributes\NoCache;
use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\ContainerBuilder;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Definition\DefinitionChain;
use Stefna\DependencyInjection\Priority;
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


	public function testDefinitionOrder(): void
	{
		$def1 = new DefinitionArray(['test1' => fn() => 1, 'test2' => fn() => 2, 'test3' => fn() => 4]);
		$def2 = new DefinitionArray(['test1' => fn() => 3, 'test4' => fn() => 12]);

		$builder = new ContainerBuilder();
		$builder->addDefinition($def1);
		$builder->addDefinition($def2);
		$builder->addDefinition([
			'test2' => fn () => 7,
		]);

		$container = $builder->build();

		$this->assertSame(3, $container->get('test1'));
		$this->assertSame(7, $container->get('test2'));
		$this->assertSame(4, $container->get('test3'));
		$this->assertSame(12, $container->get('test4'));
	}

	public function testDefaultCacheService(): void
	{
		$builder = new ContainerBuilder();
		$builder->addDefinition([
			\DateTimeInterface::class => fn () => new \DateTimeImmutable(),
		]);

		$container = $builder->build();

		$this->assertInstanceOf(Container::class, $container);

		$this->assertTrue($container->has(\DateTimeInterface::class));

		$this->assertSame(
			$container->get(\DateTimeInterface::class),
			$container->get(\DateTimeInterface::class)
		);
	}

	public function testDisableServiceCache(): void
	{
		$builder = new ContainerBuilder();
		$builder->addDefinition([
			\DateTimeInterface::class => #[NoCache] fn () => new \DateTimeImmutable(),
		]);

		$container = $builder->build();

		$this->assertInstanceOf(Container::class, $container);

		$this->assertTrue($container->has(\DateTimeInterface::class));

		$this->assertNotSame(
			$container->get(\DateTimeInterface::class),
			$container->get(\DateTimeInterface::class)
		);
	}
}
