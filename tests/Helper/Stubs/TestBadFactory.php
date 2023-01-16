<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

final class TestBadFactory
{
	public function create(): TestWithoutArgs
	{
		return new TestWithoutArgs();
	}
}
