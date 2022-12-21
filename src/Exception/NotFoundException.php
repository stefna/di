<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
	public static function withIdentifier(string $containerId): self
	{
		$message = sprintf('Could not find identifier "%s" in container', $containerId);
		return new static($message);
	}
}
