<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

final class TestFactory
{
	public function __invoke(): TestWithoutArgs
	{
		return new TestWithoutArgs();
	}
}
