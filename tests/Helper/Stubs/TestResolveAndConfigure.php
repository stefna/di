<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

final class TestResolveAndConfigure implements TestResolveInterface
{
	public function __construct(
		public string $value,
	) {}
}
