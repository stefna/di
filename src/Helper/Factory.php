<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Helper;

use Moya\DependencyInjection\Exception\BadFactoryException;
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
			return self::$cache[$hash] = fn (ContainerInterface $c) => $factory($c);
		}
		return self::$cache[$hash] = function (ContainerInterface $c) use ($factory) {
			$obj = $c->get($factory);
			if (!is_callable($obj)) {
				throw BadFactoryException::objectNotCallable();
			}
			return $obj($c);
		};
	}

	public static function full(string|object $factory): callable
	{
		$hash = is_object($factory) ? spl_object_hash($factory) : $factory;
		if (isset(self::$cache[$hash])) {
			return self::$cache[$hash];
		}
		return self::$cache[$hash] = function (ContainerInterface $c, string $className) use ($factory) {
			if (is_string($factory)) {
				$factory = $c->get($factory);
			}

			if (!is_callable($factory)) {
				throw BadFactoryException::objectNotCallable();
			}

			return $factory($c, $className);
		};
	}
}
