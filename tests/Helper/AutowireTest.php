<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper;

use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Helper\Autowire;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestInterface;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithDefaultArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithoutArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithScalarArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithUnionType;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class AutowireTest extends TestCase
{
	public function testAutowireWithoutArgument(): void
	{
		$autowire = Autowire::cls();
		$object = $autowire($this->container(), TestWithoutArgs::class);

		$this->assertInstanceOf(TestWithoutArgs::class, $object);
	}

	public function testAutowireWithSpecifiedClass(): void
	{
		$autowire = Autowire::cls(TestWithoutArgs::class);

		$object = $autowire($this->container(), TestInterface::class);

		$this->assertInstanceOf(TestInterface::class, $object);
		$this->assertInstanceOf(TestWithoutArgs::class, $object);
	}

	public function testAutowireWithDefaultArgument(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container(), TestWithDefaultArgs::class);
		$this->assertInstanceOf(TestWithDefaultArgs::class, $object);
	}

	public function testAutowireWithDefaultArgumentOverride(): void
	{
		$autowire = Autowire::cls();

		$now = new \DateTimeImmutable('2022-01-01');

		$object = $autowire($this->container([
			\DateTimeInterface::class => fn () => $now,
		]), TestWithDefaultArgs::class);
		$this->assertInstanceOf(TestWithDefaultArgs::class, $object);
		$this->assertSame($now, $object->date);
	}

	public function testAutowireParamMissingInContainer(): void
	{
		$autowire = Autowire::cls();

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Can\'t find "DateTimeImmutable" in container');

		$autowire($this->container(), TestWithArgs::class);
	}

	public function testAutowireWithScalarArgs(): void
	{
		$autowire = Autowire::cls();

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Can\'t autowire native types');

		$autowire($this->container(), TestWithScalarArgs::class);
	}

	public function testAutowireWithUnionType(): void
	{
		$autowire = Autowire::cls();

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Can\'t autowire complex types');

		$autowire($this->container(), TestWithUnionType::class);
	}

	/**
	 * @param array<class-string, callable> $def
	 */
	private function container(array $def = []): ContainerInterface
	{
		return new Container(new DefinitionArray($def));
	}
}
