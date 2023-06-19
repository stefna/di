<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper;

use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use Stefna\DependencyInjection\Exception\NotResolvedException;
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
	public function __invoke(ContainerInterface $container, string $className): object
	{
		$reflection = new \ReflectionClass($this->className ?? $className);
		$constructor = $reflection->getConstructor();
		$params = $constructor?->getParameters() ?? [];
		$args = [];
		foreach ($params as $param) {
			$type = $param->getType();
			if (!$type instanceof \ReflectionNamedType) {
				throw new NotResolvedException('Can\'t auto-wire complex types');
			}

			/** @var class-string $typeName */
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

			if (!$paramInstance && $type->isBuiltin()) {
				if ($param->isOptional()) {
					continue;
				}
				throw new NotResolvedException(sprintf(
					'Can\'t resolve argument "$%s" of type "%s"',
					$param->getName(),
					(string)$param->getType()?->__toString(),
				));
			}

			if (!$paramInstance && !$container->has($typeName)) {
				if ($param->isOptional()) {
					continue;
				}
				throw new NotResolvedException(sprintf(
					'Can\'t resolve argument "$%s" of type "%s"',
					$param->getName(),
					(string)$param->getType()?->__toString(),
				));
			}
			/** @var object $paramInstance */
			$paramInstance = $paramInstance ?? $container->get($typeName);

			if (is_object($paramInstance)) {
				$configureAttributes = $param->getAttributes(ConfigureAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
				foreach ($configureAttributes as $reflectionAttribute) {
					$attr = $reflectionAttribute->newInstance();
					$attr->configure($paramInstance, $container);
				}
			}

			$args[] = $paramInstance;
		}

		// @phpstan-ignore-next-line - don't feel like figure out how to make phpstan happy
		return $reflection->newInstance(...$args);
	}
}
