<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute\TestResolveScalarValue;

final class TestWithAttribute4
{
	public function __construct(
		#[TestResolveScalarValue('42')]
		public readonly string $test,
	) {}
}
