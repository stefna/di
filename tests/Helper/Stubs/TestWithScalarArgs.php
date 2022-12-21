<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Helper\Stubs;

final class TestWithScalarArgs implements TestInterface
{
	public function __construct(
		public bool $arg1,
	) {}
}
