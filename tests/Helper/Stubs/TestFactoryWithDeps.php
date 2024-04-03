<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Tests\Helper\Stubs;

use Stefna\DependencyInjection\Exception\BadFactoryException;
use Psr\Container\ContainerInterface;

final class TestFactoryWithDeps
{
	public function __construct(
		public \DateTimeImmutable $date,
	) {}

	public function __invoke(ContainerInterface $c): TestInterface
	{
		return new TestWithArgs($this->date);
	}
}
