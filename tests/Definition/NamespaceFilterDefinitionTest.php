<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Definition\NamespaceFilterDefinition;
use Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\AutoWire\AutoWire2;
use Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple\Class1;
use Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple\Class2;

final class NamespaceFilterDefinitionTest extends TestCase
{
	public function testStaticCreate(): void
	{
		$definition = NamespaceFilterDefinition::create('Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple');

		$this->assertCount(0, $definition->getDefinitions());

		$class1Definition = $definition->getDefinition(Class1::class);

		$this->assertNotNull($class1Definition);
		$this->assertCount(1, $definition->getDefinitions());
		$this->assertInstanceOf(Class1::class, $class1Definition($this->container(), Class1::class));
	}

	public function testStaticAutoWire(): void
	{
		$definition = NamespaceFilterDefinition::autoWire('Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\AutoWire');

		$this->assertCount(0, $definition->getDefinitions());

		$autoWire2Definition = $definition->getDefinition(AutoWire2::class);

		$this->assertNotNull($autoWire2Definition);
		$this->assertCount(1, $definition->getDefinitions());
		$this->assertInstanceOf(AutoWire2::class, $autoWire2Definition($this->container([
			Class1::class => fn () => new Class1(),
		]), AutoWire2::class));
	}

	public function testStaticFactory(): void
	{
		$definition = NamespaceFilterDefinition::factory(
			'Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple',
			fn (ContainerInterface $container, string $typeName) => new $typeName(),
		);

		$this->assertCount(0, $definition->getDefinitions());

		$autoWire2Definition = $definition->getDefinition(Class1::class);

		$this->assertNotNull($autoWire2Definition);
		$this->assertCount(1, $definition->getDefinitions());
		$this->assertInstanceOf(Class1::class, $autoWire2Definition($this->container(), Class1::class));
	}

	public function testGetReturnsSameDefinition(): void
	{
		$definition = NamespaceFilterDefinition::create('Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple');

		$this->assertCount(0, $definition->getDefinitions());

		$classDefinition1 = $definition->getDefinition(Class2::class);
		$classDefinition2 = $definition->getDefinition(Class2::class);

		$this->assertNotNull($classDefinition1);
		$this->assertNotNull($classDefinition2);
		$this->assertCount(1, $definition->getDefinitions());
		$this->assertSame($classDefinition1, $classDefinition2);
	}

	public function testNotFound(): void
	{
		$definition = NamespaceFilterDefinition::autoWire('Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\AutoWire');

		$notFoundDefinition = $definition->getDefinition(Class2::class);

		$this->assertNull($notFoundDefinition);
		$this->assertCount(0, $definition->getDefinitions());
	}

	/**
	 * @param array<class-string, callable> $def
	 */
	private function container(array $def = []): ContainerInterface
	{
		return new Container(new DefinitionArray($def));
	}
}
