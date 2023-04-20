<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

use Psr\Container\ContainerInterface;

class FilterDefinition implements DefinitionSource
{
	/** @var array<string, callable(ContainerInterface, string): mixed> */
	private array $definitions = [];

	public function __construct(
		private readonly \Closure $filter,
		private readonly \Closure $definitionFactoryFactory,
	) {}

	final public function getDefinition(string $name): ?callable
	{
		if (isset($this->definitions[$name])) {
			return $this->definitions[$name];
		}

		if (!($this->filter)($name)) {
			return null;
		}

		return $this->definitions[$name] = ($this->definitionFactoryFactory)($name);
	}

	final public function getDefinitions(): array
	{
		return $this->definitions;
	}
}
