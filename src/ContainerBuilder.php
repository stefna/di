<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Definition\DefinitionFile;
use Stefna\DependencyInjection\Definition\DefinitionSource;
use Stefna\DependencyInjection\Definition\PriorityDefinitionChain;
use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Autowire;

final class ContainerBuilder
{
	/** @var array<array-key, array{0: ContainerInterface, 1: int}> */
	private array $containers = [];

	public function __construct(
		private readonly PriorityDefinitionChain $definitionSources = new PriorityDefinitionChain(),
	) {}

	public function build(): ContainerInterface
	{
		$defaultContainer = new Container($this->definitionSources);
		if (!$this->containers) {
			return $defaultContainer;
		}

		$aggregateContainer = new AggregateContainer();
		foreach ($this->containers as [$container, $priority]) {
			$aggregateContainer->addContainer($container, $priority);
		}
		$aggregateContainer->addContainer($defaultContainer, Priority::Normal);

		return $aggregateContainer;
	}

	public function addContainer(
		ContainerInterface $container,
		Priority|int $priority = Priority::Normal,
	): self {
		$priority = is_int($priority) ? $priority : $priority->value;
		$this->containers[] = [$container, $priority];
		return $this;
	}

	/**
	 * @param DefinitionSource|string|array<string, Autowire|callable(ContainerInterface, string): mixed> $definition
	 */
	public function addDefinition(
		DefinitionSource|string|array $definition,
		Priority|int $priority = Priority::Normal,
	): self {
		if (is_string($definition)) {
			$definition = new DefinitionFile($definition);
		}
		elseif (is_array($definition)) {
			$definition = new DefinitionArray($definition);
		}
		$this->definitionSources->addDefinition($definition, $priority);

		return $this;
	}
}
