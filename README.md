# Dependency Injection

[![Build Status](https://github.com/stefna/di/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/stefna/di/actions/workflows/continuous-integration.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/stefna/di.svg)](https://packagist.org/packages/stefna/di)
[![Software License](https://img.shields.io/github/license/stefna/di.svg)](LICENSE)

This package is a lightweight dependency injection container that is framework-agnostic.

## Requirements

PHP 8.2 or higher.

## Installation

```bash
composer require stefna/di
```

## Usage

You will always have to use the `ContainerBuilder` to create a `Container` 
since we don't do any automatic autowiring, so you will have to define everything 
you want to have access to

### Configure container

To configure you container you add `DefinitionSource`'s to the container builder.

```php
<?php

use Stefna\DependencyInjection\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinition([
	ClockInterface::class => fn () => new Clock(),
]);

$container = $builder->build();

$clock = $container->get(ClockInterface::class);
```

#### Bulk configure definitions

We provide 2 definition sources that can be used to bulk define stuff.

First you have `FilterDefinition` that just allows you to provide any arbitrary filter that will be matched against
the requested class. And if it matches it will be given the definition.

Example:

```php
<?php

use Stefna\DependencyInjection\Definition\FilterDefinition;
use Stefna\DependencyInjection\Helper\Autowire;

// this will auto-wire all class that ends with Controller
$definition = new FilterDefinition(
	fn (string $className) => str_ends_with('Controller'),
	fn (string $className) => Autowires::cls($className)
);
```

We also provide a `NamespaceFilterDefinition` that makes it easier to bulk define everything in a specific namespace.

The `NamespaceFilterDefinition` just extends `FilterDefinition` and makes it easier to use with namespaces.

Example:
```php
<?php

use Stefna\DependencyInjection\Definition\NamespaceFilterDefinition;

// this will auto-wire all class that are in App\Controller namespace
$controllerDefinitions = NamespaceFilterDefinition::autoWire('App\\Controller\\');
// this will just do a simple new $className() on all classes in App\RequestInput namespace
$requestInputDefinitions = NamespaceFilterDefinition::create('App\\RequestInput\\');
```

If your going to use this to provide auto-wiring to lots of classes I would recommend extending `FilterDefintion` and
implement `PriorityAware` on the definition and return `Priority::Low` so you easily can override the "default"
definition

### Use with other container implementations

```php

<?php

use Stefna\DependencyInjection\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addContainer($externalContainer);

$container = $builder->build();

$clock = $container->get(ClockInterface::class);
```

### Autowiring

We do provide a helper that can do some lightweight autowiring.

The autowire helper will only look for dependencies in the container it will 
not try to auto create objects that aren't part of the container.

```php
<?php

use Stefna\DependencyInjection\ContainerBuilder;
use Stefna\DependencyInjection\Helper\Autowire;

interface A {}

class Obj implements A
{
	public function __construct(public readonly ClockInterface $clock)
	{}
}

$builder = new ContainerBuilder();
$builder->addDefinition([
	ClockInterface::class => fn () => new Clock(),
	Obj::class => Autowire::cls(),
	A::class => Autowire::cls(Obj::class),
]);

$container = $builder->build();

$clock = $container->get(ClockInterface::class);
```

#### Attributes

You can augment the auto-wiring with attributes. 

The auto-wire helper defaults to only fetch objects from the container.

We support 2 attribute interfaces
* `ResolverAttribute` can be used to resolve complex values from container
* `ConfigureAttribute` can be used to reconfigure an object before injecting into class

##### `ResolverAttribute`

Can be useful when you want to resolve a scalar value from something like 
a config storage.

```php
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class ConfigValue implements ResolverAttribute
{
	public function __construct(private readonly string $key) {}

	public function resolve(string $type, ContainerInterface $container): mixed
	{
		$config = $container->get(Config::class);
		return $config->get($this->key);
	}
}

final class Test
{
	public function __construct(
		#[ConfigValue('site.config.value')]
		private readonly string $configValue,
	) {}
}
```

#### `ConfigureAttribute`

Can be useful when you want to reconfigure something that being injected
for example setting a custom log channel for this class.

```php
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class LogChannel implements ConfigureAttribute
{
	public function __construct(private readonly string $channel) {}

	public function configure(object $object, ContainerInterface $container): object
	{
		if ($object instanceof LoggerInterface && class_exists(ChannelWrapper::class)) {
			return new ChannelWrapper($object, $this->channel);
		}
		if ($container->has(LoggerManager::class)) {
			return $container->get(LoggerManager::class)->createLogger($this->channel);
		}
		
		// don't know how to add channel just return the incoming logger
		return $object;
	}
}

final class Test
{
	public function __construct(
		#[LogChannel('test-channel')]
		private readonly LoggerInterface $logger,
	) {}
}
```

### Factories

Everything in the definition is in practice a factory.

But we provide a factory helper that can help with deduplicate factory 
instances and lazy instantiate the factory.

```php
<?php

use Stefna\DependencyInjection\Helper\Factory;

class ObjFactory
{
	public function __invoke(ContainerInterface $container)
	{
		return new Obj($container->get(ClockInterface::class));
	}
}

class ComplexFactory
{
	public function __invoke(ContainerInterface $container, string $className)
	{
		if ($className === A::class) {
			return new Obj($container->get(ClockInterface::class));
		}
	}
}

class AutowiredComplexFactory
{
	public function __construct(
		private ClockInterface $clock,
	) {}

	public function __invoke()
	{
		if ($className === B::class) {
			return new Obj($this->clock);
		}
	}
}

$builder->addDefinition([
	ObjFactory::class => fn () => new ObjFactory(),
	Obj::class => Factory::simple(ObjFactory::class),
	ObjFactory::class => fn () => new ComplexFactory(),
	A::class => Factory::full(ComplexFactory::class),
	B::class => Factory::autoWire(AutowiredComplexFactory::class),
]);
```

#### Autowiring factories

There are 2 ways to auto wire a factory

Option 1 add it to the container like anything else
```php
use Stefna\DependencyInjection\Helper\Autowire;
use Stefna\DependencyInjection\Helper\Factory;

$builder->addDefinition([
	ComplexFactory::class => Autowire::cls(),
	// or 
	ComplexFactory::class => fn () => new ComplexFactory(new Clock()),
	
	// looks up ComplexFactory from container and if it's not defined crashes
	A::class => Factory::full(ComplexFactory::class),
]);
```

Option 2 use the factory auto wire helper
```php
use Stefna\DependencyInjection\Helper\Autowire;
use Stefna\DependencyInjection\Helper\Factory;

$builder->addDefinition([
	A::class => Factory::autoWire(ComplexFactory::class),
]);
```
This is just a nice wrapper around option 1

## Contribute

We are always happy to receive bug/security reports and bug/security fixes

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
