<?php declare(strict_types=1);

namespace Moya\DependencyInjection;

enum Priority: int
{
	case High = 2000;
	case Normal = 1000;
	case Low = 100;
}
