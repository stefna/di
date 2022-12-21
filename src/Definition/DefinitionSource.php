<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Definition;

interface DefinitionSource
{
	/**
	 * @param string $name
	 * @return null|callable
	 */
	public function getDefinition(string $name): ?callable;

	/**
	 * @return array<string|class-string, callable>
	 */
	public function getDefinitions(): array;
}
