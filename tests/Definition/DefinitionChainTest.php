<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Definition;

use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Definition\DefinitionChain;
use Stefna\DependencyInjection\Definition\DefinitionFile;
use PHPUnit\Framework\TestCase;

final class DefinitionChainTest extends TestCase
{
	public function testGettingDefinition(): void
	{
		$def = new DefinitionChain(
			new DefinitionFile(__DIR__ . '/resources/valid-definitions.php'),
			new DefinitionArray([
				\DateTimeInterface::class => fn () => new \DateTimeImmutable('now'),
			]),
		);

		$dateFactory = $def->getDefinition(\DateTimeInterface::class);
		$this->assertNotNull($dateFactory);
		$this->assertInstanceOf(\DateTimeImmutable::class, $dateFactory(new Container($def), ''));

		foreach ($def->getDefinitions() as $defName => $factory) {
			$this->assertIsString($defName);
			$this->assertIsCallable($factory);
		}

		$this->assertCount(3, $def->getDefinitions());
	}

	public function testNotFound(): void
	{
		$chain = new DefinitionChain();
		$this->assertNull($chain->getDefinition(\DateTimeInterface::class));
	}
}
