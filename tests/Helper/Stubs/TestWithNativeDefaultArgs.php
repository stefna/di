<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

final class TestWithNativeDefaultArgs
{
	public function __construct(
		public array $memory = [],
	) {}
}
