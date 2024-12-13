<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

use Stefna\DependencyInjection\Exception\DuplicateEntryException;
use Stefna\DependencyInjection\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

final class AggregateContainer implements ContainerInterface, DelegateContainerAware
{
	/** @var ContainerInterface[][] */
	private array $containers = [];
	/** @var string[] */
	private array $ids = [];
	/** @var array<string, mixed>  */
	private array $cache = [];
	/** @var array<string, callable>  */
	private array $factoryCache = [];
	/** @var float[] */
	private array $priorities = [];
	private ContainerInterface $rootContainer;

	public function __construct()
	{
		$this->rootContainer = $this;
	}

	public function addContainer(
		ContainerInterface $container,
		Priority|int $priority = Priority::Normal,
	): void {
		$priority = is_int($priority) ? $priority : $priority->value;
		$priorityKey = "$priority.0";
		$this->containers[$priorityKey] ??= [];

		if (in_array(\spl_object_hash($container), $this->ids, true)) {
			throw new DuplicateEntryException('Duplicate container. A container can only be added once');
		}

		$this->ids[] = spl_object_hash($container);
		$this->containers[$priorityKey][] = $container;

		$this->priorities = array_keys($this->containers);
		usort($this->priorities, static fn (float $a, float $b) => $b <=> $a);

		if ($container instanceof DelegateContainerAware) {
			$container->setRootContainer($this->rootContainer);
		}
	}

	public function haveContainer(ContainerInterface $container): bool
	{
		return in_array(\spl_object_hash($container), $this->ids, true);
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $id)
	{
		if (isset($this->cache[$id])) {
			return $this->cache[$id];
		}

		if (!$this->has($id)) {
			throw NotFoundException::withIdentifier($id);
		}

		$factory = $this->factoryCache[$id];
		unset($this->factoryCache[$id]);
		return $this->cache[$id] = $factory();
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $id): bool
	{
		if (isset($this->cache[$id])) {
			return true;
		}
		if (isset($this->factoryCache[$id])) {
			return true;
		}

		foreach ($this->priorities as $priority) {
			foreach ($this->containers[$priority] as $container) {
				if ($container->has($id)) {
					$this->factoryCache[$id] = static fn () => $container->get($id);
					return true;
				}
			}
		}
		return false;
	}

	public function setRootContainer(ContainerInterface $container): void
	{
		$this->rootContainer = $container;
		foreach ($this->priorities as $priority) {
			foreach ($this->containers[$priority] as $container) {
				if ($container instanceof DelegateContainerAware) {
					$container->setRootContainer($this->rootContainer);
				}
			}
		}
	}
}
