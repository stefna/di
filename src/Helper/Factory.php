<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Helper;

use Stefna\DependencyInjection\Exception\BadFactoryException;
use Psr\Container\ContainerInterface;

final class Factory
{
	/** @var array<string, callable> */
	private static array $cache = [];

	/**
	 * @param class-string|object $factory
	 */
	public static function simple(string|object $factory): callable
	{
		$hash = is_object($factory) ? spl_object_hash($factory) : $factory;
		if (isset(self::$cache[$hash])) {
			return self::$cache[$hash];
		}

		if (is_object($factory)) {
			if (!is_callable($factory)) {
				throw BadFactoryException::objectNotCallable();
			}
			return self::$cache[$hash] = static fn (ContainerInterface $container) => $factory($container);
		}

		return self::$cache[$hash] = static function (ContainerInterface $container) use ($factory) {
			$obj = $container->get($factory);
			if (!is_callable($obj)) {
				throw BadFactoryException::objectNotCallable();
			}
			return $obj($container);
		};
	}

	public static function full(string|object $factory): callable
	{
		$hash = is_object($factory) ? spl_object_hash($factory) : $factory;

		self::$cache[$hash] ??= static function (ContainerInterface $container, string $className) use ($factory) {
			if (is_string($factory)) {
				$factory = $container->get($factory);
			}
			if (!is_callable($factory)) {
				throw BadFactoryException::objectNotCallable();
			}

			return $factory($container, $className);
		};

		return self::$cache[$hash];
	}

	/**
	 * @param class-string $factory
	 */
	public static function autoWire(string $factory): callable
	{
		$hash = $factory;

		self::$cache[$hash] ??= static function (ContainerInterface $container, string $className) use ($factory) {
			$factoryInstance = (new Autowire())($container, $factory);
			if (!is_callable($factoryInstance)) {
				throw BadFactoryException::objectNotCallable();
			}
			return $factoryInstance($container, $className);
		};

		return self::$cache[$hash];
	}
}
