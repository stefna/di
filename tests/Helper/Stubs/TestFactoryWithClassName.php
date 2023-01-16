<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Exception\BadFactoryException;
use Psr\Container\ContainerInterface;

final class TestFactoryWithClassName
{
	public function __invoke(ContainerInterface $c, string $className): TestInterface
	{
		if ($className === TestWithoutArgs::class) {
			return new TestWithoutArgs();
		}
		if ($className === TestWithArgs::class) {
			return new TestWithArgs(new \DateTimeImmutable('-2 days'));
		}

		throw BadFactoryException::classNotHandledByFactory();
	}
}
