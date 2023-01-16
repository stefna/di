<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

use Stefna\DependencyInjection\Definition\DefinitionSource;
use Stefna\DependencyInjection\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
	/** @var array<string, mixed> */
	private array $cache = [];

	public function __construct(
		private readonly DefinitionSource $definition,
	) {}

	final public function get(string $id)
	{
		if (!$this->has($id)) {
			throw NotFoundException::withIdentifier($id);
		}
		if (!isset($this->cache[$id])) {
			$factory = $this->definition->getDefinition($id);
			$this->cache[$id] = $factory ? $factory($this, $id) : null;
		}
		return $this->cache[$id];
	}

	final public function has(string $id): bool
	{
		return $this->definition->getDefinition($id) !== null;
	}
}
