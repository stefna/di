<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Definition;

use Moya\DependencyInjection\Definition\DefinitionArray;
use Moya\DependencyInjection\Definition\DefinitionChain;
use Moya\DependencyInjection\Definition\DefinitionFile;
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
		$this->assertInstanceOf(\DateTimeImmutable::class, $dateFactory());

		foreach ($def->getDefinitions() as $defName => $factory) {
			$this->assertIsString($defName);
			$this->assertIsCallable($factory);
		}

		$this->assertCount(3, $def->getDefinitions());
	}
}
