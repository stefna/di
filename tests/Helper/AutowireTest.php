<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper;

use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Helper\Autowire;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestInterface;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestResolveAndConfigure;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestResolveInterface;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithAttribute1;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithAttribute2;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithAttribute3;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithAttribute4;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithDefaultArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithNativeDefaultArgs;
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

	public function testAutoWireWithDefaultArgumentOverride(): void
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
		$this->expectExceptionMessage('Can\'t resolve argument "$date" of type "DateTimeImmutable"');

		$autowire($this->container(), TestWithArgs::class);
	}

	public function testAutowireWithScalarArgs(): void
	{
		$autowire = Autowire::cls();

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Can\'t resolve argument "$arg1" of type "bool"');

		$autowire($this->container(), TestWithScalarArgs::class);
	}

	public function testAutowireWithUnionType(): void
	{
		$autowire = Autowire::cls();

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Can\'t auto-wire complex types');

		$autowire($this->container(), TestWithUnionType::class);
	}

	public function testAutoWireWithAttributeResolver(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container([
			TestInterface::class => fn () => new TestWithScalarArgs(true),
		]), TestWithAttribute1::class);
		$this->assertInstanceOf(TestWithAttribute1::class, $object);
		$this->assertInstanceOf(TestWithArgs::class, $object->testArgs);
		$this->assertInstanceOf(TestWithDefaultArgs::class, $object->testDefault);
		$this->assertInstanceOf(TestWithScalarArgs::class, $object->testFallback);
	}

	public function testAutoWireWithAttributeConfigure(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container([
			TestResolveAndConfigure::class => fn () => new TestResolveAndConfigure('1'),
		]), TestWithAttribute2::class);
		$this->assertInstanceOf(TestWithAttribute2::class, $object);
		$this->assertSame('5', $object->test->value);
	}

	public function testAutoWireWithResolveAndConfigure(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container([
			TestResolveInterface::class => fn () => new TestResolveAndConfigure('1'),
		]), TestWithAttribute3::class);
		$this->assertInstanceOf(TestWithAttribute3::class, $object);
		$this->assertInstanceOf(TestResolveAndConfigure::class, $object->test);
		$this->assertSame('42', $object->test->value);
	}

	public function testAutoWireWithResolveScalarValue(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container(), TestWithAttribute4::class);
		$this->assertInstanceOf(TestWithAttribute4::class, $object);
		$this->assertSame('42', $object->test);
	}

	public function testAutoWireNativeTypeWithDefaultValue(): void
	{
		$autowire = Autowire::cls();

		$object = $autowire($this->container(), TestWithNativeDefaultArgs::class);
		$this->assertInstanceOf(TestWithNativeDefaultArgs::class, $object);
		$this->assertSame([], $object->memory);
	}

	/**
	 * @param array<class-string, callable> $def
	 */
	private function container(array $def = []): ContainerInterface
	{
		return new Container(new DefinitionArray($def));
	}
}
