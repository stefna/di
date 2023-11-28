<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper;

use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use Stefna\DependencyInjection\Exception\NotResolvedException;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;

trait ArgumentResolverTrait
{
	/**
	 * @return list<mixed>
	 */
	private function resolveArguments(\ReflectionMethod $method, ContainerInterface $container): array
	{
		$args = [];
		$params = $method->getParameters();
		foreach ($params as $param) {
			$args[] = $this->resolveArgument($param, $container);
		}
		return $args;
	}

	private function resolveArgument(\ReflectionParameter $param, ContainerInterface $container): mixed
	{
		$type = $param->getType();
		if (!$type instanceof \ReflectionNamedType) {
			throw new NotResolvedException(sprintf(
				"Can't auto-wire complex types.\nArgument: \"%s\"\nType: \"%s\"\nClass: \"%s\"",
				$param->getName(),
				'complex', // todo get proper type.
				$param->getDeclaringClass()?->name ?? 'unknown-class',
			));
		}

		$typeName = $type->getName();
		$resolvableAttrs = $param->getAttributes(ResolverAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
		$paramInstance = null;
		if ($resolvableAttrs) {
			foreach ($resolvableAttrs as $reflectionAttribute) {
				/** @var ResolverAttribute $attr */
				$attr = $reflectionAttribute->newInstance();
				$paramInstance = $attr->resolve($typeName, $container);
			}
		}

		if (!$paramInstance && ($type->isBuiltin() || !$container->has($typeName))) {
			return $this->resolveDefault($param);
		}
		$paramInstance = $paramInstance ?? $container->get($typeName);

		if (is_object($paramInstance)) {
			$configureAttributes = $param->getAttributes(ConfigureAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
			foreach ($configureAttributes as $reflectionAttribute) {
				$attr = $reflectionAttribute->newInstance();
				$attr->configure($paramInstance, $container);
			}
		}

		return $paramInstance;
	}

	private function resolveDefault(\ReflectionParameter $param): mixed
	{
		if ($param->isDefaultValueAvailable()) {
			return $param->getDefaultValue();
		}
		/** @var null|\ReflectionNamedType $type */
		$type = $param->getType();
		throw new NotResolvedException(sprintf(
			'Can\'t resolve argument "$%s" of type "%s" in class "%s"',
			$param->getName(),
			$type?->getName() ?? 'unknown type',
			$param->getDeclaringClass()?->name ?? 'unknown class',
		));
	}
}
