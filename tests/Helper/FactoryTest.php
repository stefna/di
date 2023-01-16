<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper;

use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Exception\BadFactoryException;
use Stefna\DependencyInjection\Helper\Factory;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestBadFactory;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestFactory;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestFactoryWithClassName;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestInterface;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithoutArgs;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class FactoryTest extends TestCase
{
	public function testFactoryWithObject(): void
	{
		$factory = Factory::simple(new TestFactory());

		$this->assertInstanceOf(TestWithoutArgs::class, $factory($this->createMock(ContainerInterface::class)));
	}

	public function testFactoryFromContainer(): void
	{
		$factory = Factory::simple(TestFactory::class);
		$container = $this->container([
			TestFactory::class => fn () => new TestFactory(),
		]);

		$this->assertInstanceOf(TestWithoutArgs::class, $factory($container));
	}

	public function testBadObjectFactory(): void
	{
		$this->expectException(BadFactoryException::class);
		Factory::simple(new TestBadFactory());
	}

	public function testBadObjectFactoryFromContainer(): void
	{
		$this->expectException(BadFactoryException::class);
		$container = $this->container([
			TestBadFactory::class => fn () => new TestBadFactory(),
		]);
		$factory = Factory::simple(TestBadFactory::class);
		$factory($container);
	}

	public function testFullFactory(): void
	{
		$factory = Factory::full(new TestFactoryWithClassName());

		$this->assertInstanceOf(TestWithoutArgs::class, $factory($this->container(), TestWithoutArgs::class));
		$this->assertInstanceOf(TestWithArgs::class, $factory($this->container(), TestWithArgs::class));
	}

	public function testExceptionIfFactoryDontCreateObject(): void
	{
		$this->expectException(BadFactoryException::class);

		$factory = Factory::full(TestFactoryWithClassName::class);
		$factory($this->container([
			TestFactoryWithClassName::class => fn () => new TestFactoryWithClassName(),
		]), TestInterface::class);
	}

	public function testBadObjectFactoryFromContainerInFullFactory(): void
	{
		$this->expectException(BadFactoryException::class);
		$container = $this->container([
			TestBadFactory::class => fn () => new TestBadFactory(),
		]);
		$factory = Factory::full(TestBadFactory::class);
		$factory($container, TestInterface::class);
	}

	public function testFactoryCache(): void
	{
		$factory1 = Factory::simple(TestFactory::class);
		$factory2 = Factory::simple(TestFactory::class);

		$this->assertSame($factory1, $factory2);
	}

	/**
	 * @param array<class-string, callable> $def
	 */
	private function container(array $def = []): ContainerInterface
	{
		return new Container(new DefinitionArray($def));
	}
}
