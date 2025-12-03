<?php declare(strict_types=1);

namespace Stefna\DependencyInjection\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_FUNCTION)]
final class NoCache
{
}
