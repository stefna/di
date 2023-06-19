<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper;

use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use Stefna\DependencyInjection\Exception\NotResolvedException;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;

final class Autowire
{
	use ArgumentResolverTrait;

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
		$className = $this->className ?? $className;
		$reflection = new \ReflectionClass($className);
		$constructor = $reflection->getConstructor();
		$params = $constructor?->getParameters() ?? [];
		$args = [];
		foreach ($params as $param) {
			$args[] = $this->resolveArgument($param, $container);
		}

		/** @var T of object */
		return new $className(...$args);
	}
}
