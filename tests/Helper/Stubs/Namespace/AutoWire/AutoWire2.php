<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\AutoWire;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Namespace\Simple\Class1;

final class AutoWire2
{
	public function __construct(
		public Class1 $class1,
	) {}
}
