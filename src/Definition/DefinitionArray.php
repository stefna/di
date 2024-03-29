<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

use Psr\Container\ContainerInterface;

class DefinitionArray implements DefinitionSource
{
	/**
	 * @param array<string|class-string, callable(ContainerInterface, class-string): mixed> $definitions
	 */
	public function __construct(
		private array $definitions,
	) {
		if ($this->definitions && array_is_list($this->definitions)) {
			throw new \BadMethodCallException('Definitions must be indexed by entry name');
		}
	}

	/**
	 * @return array<string|class-string, callable(ContainerInterface, class-string): mixed>
	 */
	public function getDefinitions(): array
	{
		return $this->definitions;
	}

	/**
	 * @param array<string|class-string, callable(ContainerInterface, class-string): mixed> $definitions
	 */
	public function addDefinitions(array $definitions): void
	{
		if (array_is_list($definitions)) {
			throw new \BadMethodCallException('Definitions must be indexed by entry name');
		}

		// The newly added data prevails
		// "for keys that exist in both arrays, the elements from the left-hand array will be used"
		$this->definitions = $definitions + $this->definitions;
	}

	public function getDefinition(string $name): ?callable
	{
		return $this->definitions[$name] ?? null;
	}
}
