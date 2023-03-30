<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute\TestConfigureAttribute;

final class TestWithAttribute2
{
	public function __construct(
		#[TestConfigureAttribute('5')]
		public readonly TestResolveAndConfigure $test,
	) {}
}
