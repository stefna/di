<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

final class DefinitionChain implements DefinitionSource
{
	/** @var DefinitionSource[] */
	private readonly array $definitions;

	public function __construct(
		DefinitionSource ...$definitions,
	) {
		$this->definitions = $definitions;
	}

	public function getDefinitions(): array
	{
		$definitions = [];
		foreach ($this->definitions as $source) {
			$definitions[] = $source->getDefinitions();
		}

		return array_merge(...$definitions);
	}

	public function getDefinition(string $name): ?callable
	{
		foreach ($this->definitions as $definition) {
			$factory = $definition->getDefinition($name);
			if ($factory) {
				return $factory;
			}
		}
		return null;
	}
}
