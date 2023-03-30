<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs\Attribute;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Tests\Helper\Stubs\TestResolveAndConfigure;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TestConfigureAttribute implements ConfigureAttribute
{
	public function __construct(
		public string $value,
	) {}

	public function configure(object $object, ContainerInterface $container): object
	{
		if ($object instanceof TestResolveAndConfigure) {
			$object->value = $this->value;
		}

		return $object;
	}
}
