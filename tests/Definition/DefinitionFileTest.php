<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Definition;

use Moya\DependencyInjection\Definition\DefinitionFile;
use PHPUnit\Framework\TestCase;

final class DefinitionFileTest extends TestCase
{
	public function testValidFile(): void
	{
		$def = new DefinitionFile(__DIR__ . '/resources/valid-definitions.php');

		$this->assertCount(2, $def->getDefinitions());

		$factory = $def->getDefinition(\ArrayObject::class);
		$this->assertNotNull($factory);
		$this->assertInstanceOf(\ArrayObject::class, $factory());
	}

	public function testInvalidFile(): void
	{
		$def = new DefinitionFile(__DIR__ . '/resources/invalid-definitions.php');

		$this->expectException(\BadMethodCallException::class);

		$def->getDefinition(\ArrayObject::class);
	}

	public function testFileNotFound(): void
	{
		$def = new DefinitionFile(__DIR__ . '/resources/not-found-definitions.php');
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/File '.*\/not-found-definitions.php' not found/");

		$def->getDefinitions();
	}

	public function testInvalidContent(): void
	{
		$def = new DefinitionFile(__DIR__ . '/resources/invalid-definitions-content.php');
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches("/File '.*\/invalid-definitions-content.php' should return an array of definitions/");

		$def->getDefinitions();
	}
}
