<?php declare(strict_types=1);

namespace Stefna\DependencyInjection;

enum Priority: int
{
	case High = 2000;
	case Normal = 1000;
	case Low = 100;
}
