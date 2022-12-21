<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Tests\Helper\Stubs;

final class TestBadFactory
{
	public function create(): TestWithoutArgs
	{
		return new TestWithoutArgs();
	}
}
