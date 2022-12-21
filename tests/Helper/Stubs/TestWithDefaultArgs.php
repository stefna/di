<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Helper\Stubs;

final class TestWithDefaultArgs implements TestInterface
{
	public function __construct(
		public \DateTimeInterface $date = new \DateTimeImmutable(),
	) {}

	public function doStuff(): bool
	{
		return true;
	}
}
