<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

use Psr\Container\ContainerInterface;

interface DefinitionSource
{
	/**
	 * @param string $name
	 * @return null|callable(ContainerInterface, string): mixed
	 */
	public function getDefinition(string $name): ?callable;

	/**
	 * @return array<string|class-string, callable>
	 */
	public function getDefinitions(): array;
}
