<?php

declare(strict_types=1);

namespace Mtubis\MTCollections\Tests;

use InvalidArgumentException;
use LogicException;
use Mtubis\MTCollections\SortedLinkedList;
use PHPUnit\Framework\TestCase;

final class SortedLinkedListTest extends TestCase
{
    public function testItKeepsIntsSortedAndAllowsDuplicates(): void
    {
        $list = SortedLinkedList::forInts();

        $list->add(5)->add(2)->add(4)->add(4)->add(1);

        self::assertSame([1, 2, 4, 4, 5], $list->toArray());
        self::assertSame(5, $list->count());
        self::assertSame(SortedLinkedList::TYPE_INT, $list->type());
    }

    public function testItKeepsStringsSorted(): void
    {
        $list = SortedLinkedList::forStrings();

        $list->add('Zoe')->add('Anna')->add('Mike');

        self::assertSame(['Anna', 'Mike', 'Zoe'], $list->toArray());
        self::assertSame(SortedLinkedList::TYPE_STRING, $list->type());
    }

    public function testTypeIsInferredOnFirstAdd(): void
    {
        $list = SortedLinkedList::create();

        $list->add(10);
        self::assertSame(SortedLinkedList::TYPE_INT, $list->type());

        self::assertSame([10], $list->toArray());
    }

    public function testItRejectsMixedTypes(): void
    {
        $list = SortedLinkedList::create();
        $list->add(1);

        $this->expectException(InvalidArgumentException::class);
        $list->add('oops');
    }

    public function testContainsUsesEarlyExit(): void
    {
        $list = SortedLinkedList::forInts();
        $list->add(10)->add(20)->add(30);

        self::assertTrue($list->contains(20));
        self::assertFalse($list->contains(5));   // < head
        self::assertFalse($list->contains(25));  // between
        self::assertFalse($list->contains(99));  // > last
    }

    public function testRemoveRemovesFirstOccurrenceOnly(): void
    {
        $list = SortedLinkedList::forInts();
        $list->add(2)->add(2)->add(2)->add(1)->add(3);

        self::assertTrue($list->remove(2));
        self::assertSame([1, 2, 2, 3], $list->toArray());
        self::assertSame(4, $list->count());
    }

    public function testRemoveAllRemovesAllOccurrences(): void
    {
        $list = SortedLinkedList::forInts();
        $list->add(2)->add(2)->add(1)->add(3)->add(2);

        self::assertSame(3, $list->removeAll(2));
        self::assertSame([1, 3], $list->toArray());
        self::assertSame(2, $list->count());
    }

    public function testFirstAndLastThrowOnEmpty(): void
    {
        $list = SortedLinkedList::forInts();

        $this->expectException(LogicException::class);
        $list->first();
    }

    public function testFirstAndLastReturnExpectedValues(): void
    {
        $list = SortedLinkedList::forInts();
        $list->add(5)->add(1)->add(3);

        self::assertSame(1, $list->first());
        self::assertSame(5, $list->last());
    }

    public function testClearKeepsTypeButRemovesElements(): void
    {
        $list = SortedLinkedList::forStrings();
        $list->add('b')->add('a');

        $list->clear();

        self::assertSame([], $list->toArray());
        self::assertSame(0, $list->count());
        self::assertSame(SortedLinkedList::TYPE_STRING, $list->type());
    }

    public function testCustomComparatorForStrings(): void
    {
        $list = SortedLinkedList::forStrings(
            static fn (string $a, string $b): int => strcasecmp($a, $b),
        );

        $list->add('zoe')->add('Anna')->add('mike');

        // case-insensitive ordering
        self::assertSame(['Anna', 'mike', 'zoe'], $list->toArray());
    }
}
