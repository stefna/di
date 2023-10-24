<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Stefna\DependencyInjection\Definition\PriorityDefinitionArray;
use Stefna\DependencyInjection\Priority;

final class PriorityDefinitionArrayTest extends TestCase
{
	public function testPriorityEnum(): void
	{
		$def = new PriorityDefinitionArray([
			'test' => fn () => 'test',
		], Priority::High);

		$this->assertCount(1, $def->getDefinitions());
		$this->assertSame(Priority::High, $def->getPriority());
	}

	public function testPriorityInt(): void
	{
		$priority = 1500;
		$def = new PriorityDefinitionArray([
			'test' => fn () => 'test',
		], $priority);

		$this->assertCount(1, $def->getDefinitions());
		$this->assertSame($priority, $def->getPriority());
	}
}
