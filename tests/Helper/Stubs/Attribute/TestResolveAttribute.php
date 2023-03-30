<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithArgs;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestWithDefaultArgs;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TestResolveAttribute implements ResolverAttribute
{
	public function __construct(
		private string $type,
	) {}

	public function resolve(string $type, ContainerInterface $container)
	{
		$date = new \DateTimeImmutable();
		return match($this->type) {
			'args' => new TestWithArgs($date),
			'default' => new TestWithDefaultArgs(),
			default => null,
		};
	}
}
