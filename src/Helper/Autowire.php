<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Helper;

use Psr\Container\ContainerInterface;

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
			$containerHasType = $c->has($type->getName());
			if (!$containerHasType && !$param->isOptional()) {
				throw new \BadMethodCallException(sprintf('Can\'t find "%s" in container', $type->getName()));
			}

			if ($containerHasType) {
				$args[] = $c->get($type->getName());
			}
		}

		// @phpstan-ignore-next-line - don't feel like figure out how to make phpstan happy
		return $reflection->newInstance(...$args);
	}
}
