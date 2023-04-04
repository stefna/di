<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute\TestResolveAttribute;

final class TestWithAttribute1
{
	public function __construct(
		#[TestResolveAttribute('args')]
		public readonly TestInterface $testArgs,
		#[TestResolveAttribute('default')]
		public readonly TestInterface $testDefault,
		#[TestResolveAttribute('unknown')]
		public readonly TestInterface $testFallback,
	) {}
}
