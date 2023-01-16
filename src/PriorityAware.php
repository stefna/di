<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

interface PriorityAware
{
	public function getPriority(): Priority|int;
}
