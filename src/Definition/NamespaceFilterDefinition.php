<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Definition;

use Psr\Container\ContainerInterface;
use Stefna\DependencyInjection\Helper\Autowire;

final class NamespaceFilterDefinition extends FilterDefinition
{
	/**
	 * @param callable(ContainerInterface, string): callable $factory
	 */
	public static function factory(string $namespace, callable $factory): DefinitionSource
	{
		return new self(
			$namespace,
			fn () => $factory(...),
		);
	}

	public static function autoWire(string $namespace): DefinitionSource
	{
		return new self(
			$namespace,
			fn () => Autowire::cls(),
		);
	}

	public static function create(string $namespace): DefinitionSource
	{
		return new self(
			$namespace,
			fn (string $name) => fn () => new $name(),
		);
	}

	public function __construct(
		string $namespace,
		\Closure $definitionFactory,
	) {
		parent::__construct(
			fn (string $name) => str_starts_with($name, $namespace),
			$definitionFactory,
		);
	}
}
