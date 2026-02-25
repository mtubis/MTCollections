<?php

declare(strict_types=1);

namespace Mtubis\MTCollections\Internal;

/**
 * @internal
 *
 * @template T of int|string
 */
final class Node
{
    /** @var T */
    public int|string $value;

    /** @var Node<T>|null */
    public ?Node $next = null;

    /**
     * @param T $value
     */
    public function __construct(int|string $value)
    {
        $this->value = $value;
    }
}
