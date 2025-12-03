<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

use Stefna\DependencyInjection\Attributes\NoCache;
use Stefna\DependencyInjection\Definition\DefinitionSource;
use Stefna\DependencyInjection\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, DelegateContainerAware
{
	/** @var array<string, mixed> */
	private array $cache = [];
	private ContainerInterface $rootContainer;

	public function __construct(
		private readonly DefinitionSource $definition,
	) {
		$this->rootContainer = $this;
	}

	public function setRootContainer(ContainerInterface $container): void
	{
		$this->rootContainer = $container;
	}

	/**
	 * @template T
	 * @param class-string<T> $id
	 * @return T
	 */
	final public function get(string $id)
	{
		if (!$this->has($id)) {
			throw NotFoundException::withIdentifier($id);
		}
		if (!isset($this->cache[$id])) {
			$factory = $this->definition->getDefinition($id);
			$reflection = null;
			if ($factory instanceof \Closure) {
				$reflection = new \ReflectionFunction($factory);
			}
			elseif (is_object($factory)) {
				$reflection = new \ReflectionClass($factory);
			}
			if ($reflection?->getAttributes(NoCache::class) && $factory) {
				return $factory($this->rootContainer, $id);
			}
			$this->cache[$id] = $factory ? $factory($this->rootContainer, $id) : null;
		}
		return $this->cache[$id];
	}

	final public function has(string $id): bool
	{
		return $this->definition->getDefinition($id) !== null;
	}
}
