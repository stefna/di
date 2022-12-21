<?php declare(strict_types=1);

namespace Moya\DependencyInjection;

interface PriorityAware
{
	public function getPriority(): Priority|int;
}
