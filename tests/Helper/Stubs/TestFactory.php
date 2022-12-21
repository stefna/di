<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Helper\Stubs;

final class TestFactory
{
	public function __invoke(): TestWithoutArgs
	{
		return new TestWithoutArgs();
	}
}
