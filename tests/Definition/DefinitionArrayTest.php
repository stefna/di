<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Definition;

use Stefna\DependencyInjection\Definition\DefinitionArray;
use PHPUnit\Framework\TestCase;

final class DefinitionArrayTest extends TestCase
{
	public function testBasicUsage(): void
	{
		$factory = fn () => 'test';
		$def = new DefinitionArray([
			'test' => $factory,
		]);

		$this->assertCount(1, $def->getDefinitions());

		$retriedFactory = $def->getDefinition('test');
		$this->assertIsCallable($retriedFactory);
		$this->assertSame($factory, $retriedFactory);
	}

	public function testConstructorWithInvalidDefinitionArray(): void
	{
		$this->expectException(\BadMethodCallException::class);
		// @phpstan-ignore-next-line - it's testing so it can't happen
		new DefinitionArray([
			fn () => 'test',
		]);
	}

	public function testAddDefinitions(): void
	{
		$factory = fn () => 'test';
		$def = new DefinitionArray([
			'test' => $factory,
		]);

		$def->addDefinitions([
			'test2' => $factory,
			'test3' => $factory,
		]);

		$this->assertCount(3, $def->getDefinitions());
	}

	public function testAddInvalidDefinitions(): void
	{
		$factory = fn () => 'test';
		$def = new DefinitionArray([
			'test' => $factory,
		]);

		$this->expectException(\BadMethodCallException::class);

		// @phpstan-ignore-next-line - it's testing so it can't happen
		$def->addDefinitions([
			$factory,
			$factory,
		]);
	}

	public function testEmptyConstructor(): void
	{
		$def = new DefinitionArray([]);

		$this->assertCount(0, $def->getDefinitions());
	}
}
