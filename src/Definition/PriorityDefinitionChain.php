<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Definition;

use Moya\DependencyInjection\Exception\DuplicateEntryException;
use Moya\DependencyInjection\Priority;
use Moya\DependencyInjection\PriorityAware;

final class PriorityDefinitionChain implements DefinitionSource
{
	/** @var DefinitionSource[][] */
	private array $definitions = [];
	/** @var string[] */
	private array $ids = [];
	/** @var float[] */
	private array $priorities = [];

	public function addDefinition(
		DefinitionSource $definitionSource,
		Priority|int $priority = Priority::Normal,
	): void {
		if ($definitionSource instanceof PriorityAware) {
			$priority = $definitionSource->getPriority();
		}

		$priority = is_int($priority) ? $priority : $priority->value;
		$priorityKey = "$priority.0";
		$this->definitions[$priorityKey] ??= [];

		if (in_array(\spl_object_hash($definitionSource), $this->ids, true)) {
			throw new DuplicateEntryException('Duplicate definition. A definition can only be added once');
		}

		$this->ids[] = spl_object_hash($definitionSource);
		$this->definitions[$priorityKey][] = $definitionSource;

		$this->priorities = array_keys($this->definitions);
		usort($this->priorities, static fn ($a, $b) => $b <=> $a);
	}

	public function haveDefinition(DefinitionSource $definitionSource): bool
	{
		return in_array(\spl_object_hash($definitionSource), $this->ids, true);
	}

	public function getDefinition(string $name): ?callable
	{
		foreach ($this->priorities as $priority) {
			foreach ($this->definitions[$priority] as $definition) {
				$factory = $definition->getDefinition($name);
				if ($factory) {
					return $factory;
				}
			}
		}

		return null;
	}

	public function getDefinitions(): array
	{
		$definitions = [];
		foreach (array_reverse($this->priorities) as $priority) {
			foreach ($this->definitions[$priority] as $def) {
				$definitions[] = $def->getDefinitions();
			}
		}

		return array_merge(...$definitions);
	}
}
