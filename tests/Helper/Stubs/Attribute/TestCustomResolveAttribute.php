<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestResolveAndConfigure;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TestCustomResolveAttribute implements ResolverAttribute
{
	public function resolve(string $type, ContainerInterface $container)
	{
		return new TestResolveAndConfigure('2');
	}
}
