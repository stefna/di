<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper\Attribute;

use Psr\Container\ContainerInterface;

interface ResolverAttribute
{
	public function resolve(string $type, ContainerInterface $container): mixed;
}
