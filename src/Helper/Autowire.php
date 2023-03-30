<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;

final class Autowire
{
	/**
	 * @param class-string|null $className
	 */
	public static function cls(?string $className = null): self
	{
		return new self($className);
	}

	public function __construct(
		/** @var class-string */
		private readonly ?string $className = null,
	) {}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return T
	 */
	public function __invoke(ContainerInterface $c, string $className): object
	{
		$reflection = new \ReflectionClass($this->className ?? $className);
		$constructor = $reflection->getConstructor();
		$params = $constructor?->getParameters() ?? [];
		$args = [];
		foreach ($params as $param) {
			$type = $param->getType();
			if (!$type instanceof \ReflectionNamedType) {
				throw new \BadMethodCallException('Can\'t autowire complex types');
			}
			if ($type->isBuiltin()) {
				throw new \BadMethodCallException('Can\'t autowire native types');
			}

			/** @var class-string $typeName */
			$typeName = $type->getName();
			$containerHasType = $c->has($typeName);
			if (!$containerHasType && !$param->isOptional()) {
				throw new \BadMethodCallException(sprintf('Can\'t find "%s" in container', $type->getName()));
			}

			$paramInstance = null;
			if ($param->getAttributes()) {
				foreach ($param->getAttributes() as $reflectionAttribute) {
					$implements = class_implements($reflectionAttribute->getName());
					if (in_array(ResolverAttribute::class, $implements, true)) {
						/** @var ResolverAttribute $attr */
						$attr = $reflectionAttribute->newInstance();
						$paramInstance = $attr->resolve($typeName, $c);
					}
					if (in_array(ConfigureAttribute::class, $implements, true)) {
						if (!$paramInstance) {
							/** @var object $paramInstance */
							$paramInstance = $c->get($typeName);
						}
						/** @var ConfigureAttribute $attr */
						$attr = $reflectionAttribute->newInstance();
						$attr->configure($paramInstance, $c);
					}
				}
			}

			if ($containerHasType) {
				$args[] = $paramInstance ?? $c->get($type->getName());
			}
		}

		// @phpstan-ignore-next-line - don't feel like figure out how to make phpstan happy
		return $reflection->newInstance(...$args);
	}
}
