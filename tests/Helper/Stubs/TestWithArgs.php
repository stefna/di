<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

final class TestWithArgs implements TestInterface
{
	public function __construct(
		public \DateTimeImmutable $date,
	) {}
}
