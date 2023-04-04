<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute\TestConfigureAttribute;
use Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute\TestCustomResolveAttribute;

final class TestWithAttribute3
{
	public function __construct(
		#[TestCustomResolveAttribute]
		#[TestConfigureAttribute('42')]
		public readonly TestResolveInterface $test,
	) {}
}
