<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Exception;

final class BadFactoryException extends \BadMethodCallException
{
	public static function objectNotCallable(): self
	{
		return new self('Bad factory. Object is not callable');
	}

	public static function classNotHandledByFactory(): self
	{
		return new self('Wrong factory. Class is not created by factory');
	}
}
