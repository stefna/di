<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\AutoWire;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple\Class2;

final class AutoWire1
{
	public function __construct(
		public Class2 $class1,
	) {}
}
