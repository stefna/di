<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

use Psr\Container\ContainerInterface;

interface DelegateContainerAware
{
	public function setRootContainer(ContainerInterface $container): void;
}
