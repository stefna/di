<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper\Attribute;

use Psr\Container\ContainerInterface;

interface ConfigureAttribute
{
	public function configure(object $object, ContainerInterface $container): object;
}
