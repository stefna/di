<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

use Stefna\DependencyInjection\Exception\DuplicateEntryException;
use Stefna\DependencyInjection\Priority;
use Stefna\DependencyInjection\PriorityAware;

final class PriorityDefinitionArray extends DefinitionArray implements PriorityAware
{
	public function __construct(
		array $definitions,
		private Priority|int $priority,
	) {
		parent::__construct($definitions);
	}

	public function getPriority(): Priority|int
	{
		return $this->priority;
	}
}
