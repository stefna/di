<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TestResolveScalarValue implements ResolverAttribute
{
	public function __construct(
		private string $value,
	) {}

	public function resolve(string $type, ContainerInterface $container): mixed
	{
		return $this->value;
	}
}
