<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Psr\Container\ContainerInterface;

final class TestWithContainer
{
	public function __construct(
		public ContainerInterface $container,
	) {}
}
