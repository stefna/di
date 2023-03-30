<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper\Attribute;

use Psr\Container\ContainerInterface;

interface ResolverAttribute
{
	/**
	 * @template T
	 * @param class-string<T> $type
	 * @return T
	 */
	public function resolve(string $type, ContainerInterface $container);
}
