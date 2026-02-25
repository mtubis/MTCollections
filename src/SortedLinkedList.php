<?php

declare(strict_types=1);

namespace Mtubis\MTCollections;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Mtubis\MTCollections\Internal\Node;
use Traversable;

/**
 * Sorted singly linked list (ascending).
 *
 * - Holds either ints or strings (not both).
 * - Keeps elements sorted on insertion.
 * - Allows duplicates.
 *
 * @implements IteratorAggregate<int, int|string>
 */
final class SortedLinkedList implements Countable, IteratorAggregate
{
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';

    /** @var self::TYPE_INT|self::TYPE_STRING|null */
    private ?string $type;

    private ?Node $head = null;

    private int $count = 0;

    /**
     * Comparator signature like usort: <0, 0, >0.
     *
     * We accept:
     * - callable(int, int): int (for int lists)
     * - callable(string, string): int (for string lists)
     * - callable(int|string, int|string): int (generic)
     *
     * @var (callable(int, int): int)|(callable(string, string): int)|(callable(int|string, int|string): int)|null
     */
    private $comparator;

    /**
     * @param self::TYPE_INT|self::TYPE_STRING|null                                                                  $type
     * @param (callable(int, int): int)|(callable(string, string): int)|(callable(int|string, int|string): int)|null $comparator
     */
    public function __construct(?string $type = null, ?callable $comparator = null)
    {
        if ($type !== null && $type !== self::TYPE_INT && $type !== self::TYPE_STRING) {
            throw new InvalidArgumentException('Type must be "int", "string", or null.');
        }

        $this->type = $type;
        $this->comparator = $comparator;
    }

    public static function create(): self
    {
        return new self(null, null);
    }

    /**
     * @param callable(int, int): int|null $comparator
     */
    public static function forInts(?callable $comparator = null): self
    {
        return new self(self::TYPE_INT, $comparator);
    }

    /**
     * @param callable(string, string): int|null $comparator
     */
    public static function forStrings(?callable $comparator = null): self
    {
        return new self(self::TYPE_STRING, $comparator);
    }

    public function add(int|string $value): self
    {
        $this->ensureTypeOnAdd($value);

        $new = new Node($value);

        if ($this->head === null) {
            $this->head = $new;
            $this->count = 1;

            return $this;
        }

        // Insert at head if value <= head value (stable: new before equals).
        if ($this->compare($value, $this->head->value) <= 0) {
            $new->next = $this->head;
            $this->head = $new;
            $this->count++;

            return $this;
        }

        // Find insertion point: prev.value < value <= curr.value
        $prev = $this->head;
        $curr = $this->head->next;

        while ($curr !== null && $this->compare($value, $curr->value) > 0) {
            $prev = $curr;
            $curr = $curr->next;
        }

        $new->next = $curr;
        $prev->next = $new;
        $this->count++;

        return $this;
    }

    public function remove(int|string $value): bool
    {
        $this->ensureTypeOnReadOperation($value);

        if ($this->head === null) {
            return false;
        }

        // Early exit: sorted ascending
        if ($this->compare($value, $this->head->value) < 0) {
            return false;
        }

        if ($this->compare($value, $this->head->value) === 0) {
            $this->head = $this->head->next;
            $this->count--;

            return true;
        }

        $prev = $this->head;
        $curr = $this->head->next;

        while ($curr !== null) {
            $cmp = $this->compare($value, $curr->value);

            if ($cmp === 0) {
                $prev->next = $curr->next;
                $this->count--;

                return true;
            }

            // If value < curr => it will never appear later
            if ($cmp < 0) {
                return false;
            }

            $prev = $curr;
            $curr = $curr->next;
        }

        return false;
    }

    public function removeAll(int|string $value): int
    {
        $removed = 0;

        while ($this->remove($value)) {
            $removed++;
        }

        return $removed;
    }

    public function contains(int|string $value): bool
    {
        $this->ensureTypeOnReadOperation($value);

        $curr = $this->head;

        while ($curr !== null) {
            $cmp = $this->compare($value, $curr->value);

            if ($cmp === 0) {
                return true;
            }

            if ($cmp < 0) {
                return false;
            }

            $curr = $curr->next;
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    public function first(): int|string
    {
        if ($this->head === null) {
            throw new LogicException('List is empty.');
        }

        return $this->head->value;
    }

    public function last(): int|string
    {
        if ($this->head === null) {
            throw new LogicException('List is empty.');
        }

        $curr = $this->head;
        while ($curr->next !== null) {
            $curr = $curr->next;
        }

        return $curr->value;
    }

    /**
     * Clears list nodes but keeps the established type (predictable library behavior).
     */
    public function clear(): void
    {
        $this->head = null;
        $this->count = 0;
    }

    /**
     * @return array<int, int|string>
     */
    public function toArray(): array
    {
        $out = [];
        foreach ($this as $value) {
            $out[] = $value;
        }

        return $out;
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return Traversable<int, int|string>
     */
    public function getIterator(): Traversable
    {
        $curr = $this->head;

        while ($curr !== null) {
            yield $curr->value;
            $curr = $curr->next;
        }
    }

    /**
     * @return self::TYPE_INT|self::TYPE_STRING|null
     */
    public function type(): ?string
    {
        return $this->type;
    }

    // -----------------------
    // Internals
    // -----------------------

    private function compare(int|string $a, int|string $b): int
    {
        if ($this->comparator !== null) {
            return (int) ($this->comparator)($a, $b);
        }

        if (is_int($a) && is_int($b)) {
            return $a <=> $b;
        }

        if (is_string($a) && is_string($b)) {
            return $a <=> $b;
        }

        // Should not happen if type is enforced correctly.
        throw new LogicException('Internal error: attempted to compare mixed types.');
    }

    private function ensureTypeOnAdd(int|string $value): void
    {
        $incoming = is_int($value) ? self::TYPE_INT : self::TYPE_STRING;

        if ($this->type === null) {
            $this->type = $incoming;

            return;
        }

        if ($this->type !== $incoming) {
            throw new InvalidArgumentException(sprintf(
                'This list holds only %s values; %s given.',
                $this->type,
                $incoming,
            ));
        }
    }

    /**
     * For read-like operations (contains/remove): do not lock the list type if still null.
     */
    private function ensureTypeOnReadOperation(int|string $value): void
    {
        if ($this->type === null) {
            return;
        }

        $incoming = is_int($value) ? self::TYPE_INT : self::TYPE_STRING;

        if ($this->type !== $incoming) {
            throw new InvalidArgumentException(sprintf(
                'This list holds only %s values; %s given.',
                $this->type,
                $incoming,
            ));
        }
    }
}
