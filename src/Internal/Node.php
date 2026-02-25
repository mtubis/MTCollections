<?php

declare(strict_types=1);

namespace Mtubis\MTCollections\Internal;

/**
 * @internal
 */
final class Node
{
    public int|string $value;

    public ?Node $next = null;

    public function __construct(int|string $value)
    {
        $this->value = $value;
    }
}
