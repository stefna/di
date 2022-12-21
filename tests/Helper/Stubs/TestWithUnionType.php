<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Helper\Stubs;

final class TestWithUnionType implements TestInterface
{
	public function __construct(
		public \DateTime|\DateTimeImmutable $date,
	) {}
}
