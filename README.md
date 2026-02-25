# MTCollections

Small PHP collections/data-structures library.

Currently includes:

- `SortedLinkedList` — singly linked list that keeps values **sorted ascending** on insertion.

## Requirements

- PHP `^8.2`

## Installation

Install via Composer:

```bash
composer require mtubis/mtcollections
```

## Quick start

### Integers

```php
use Mtubis\MTCollections\SortedLinkedList;

$list = SortedLinkedList::forInts();

$list->add(5)->add(2)->add(4)->add(4);

var_dump($list->toArray()); // [2, 4, 4, 5]
var_dump($list->contains(4)); // true

$list->remove(4); // removes first occurrence
var_dump($list->toArray()); // [2, 4, 5]
```

### Strings

```php
use Mtubis\MTCollections\SortedLinkedList;

$names = SortedLinkedList::forStrings();

$names->add('Zoe')->add('Anna')->add('Mike');

var_dump($names->toArray()); // ['Anna', 'Mike', 'Zoe']
```

### Type inference (default factory)

If you create a list without explicitly selecting the type, it will be inferred from the first add():

```php
use Mtubis\MTCollections\SortedLinkedList;

$list = SortedLinkedList::create();
$list->add(10);

echo $list->type(); // "int"
```

## SortedLinkedList behavior

### Key properties

- The list is always sorted ascending.

- It can hold either int or string values, but not both.

- Duplicates are allowed.

- Iterable via foreach and countable via count($list).

### API

- add(int|string $value): self

  Inserts element preserving sort order.

- remove(int|string $value): bool

  Removes first occurrence, returns true if removed.

- removeAll(int|string $value): int

  Removes all occurrences, returns number removed.

- contains(int|string $value): bool

- first(): int|string

  Returns first element. Throws LogicException on empty list.

- last(): int|string

  Returns last element. Throws LogicException on empty list.

- toArray(): array

- clear(): void

  Clears nodes, keeps list type (predictable library behavior).

- type(): ?string

  Returns "int", "string", or null (if not inferred yet).

### Type safety

- If a list is typed as int, inserting a string throws InvalidArgumentException (and vice-versa).

- Read operations (contains, remove) do not “lock” type when list is still empty and type is null.

### Custom comparator

You can pass your own comparator (same contract as usort comparator: <0, 0, >0).

#### Example: case-insensitive sorting for strings

```php
use Mtubis\MTCollections\SortedLinkedList;

$list = SortedLinkedList::forStrings(
    static fn (string $a, string $b): int => strcasecmp($a, $b)
);

$list->add('zoe')->add('Anna')->add('mike');

var_dump($list->toArray()); // ['Anna', 'mike', 'zoe']
```

Note: the list still enforces a single value type (int or string) even with a custom comparator.

## Development

### Install dev dependencies

```bash
composer install
```

### Run tests

```bash
composer test
```

### Static analysis (PHPStan)

```bash
composer stan
```

### Coding style (PHP-CS-Fixer)

```bash
composer cs:check
composer cs:fix
```

### Full QA (style + static analysis + tests)

```bash
composer qa
```

## Project structure

```code
src/
  SortedLinkedList.php
  Internal/
    Node.php
tests/
  SortedLinkedListTest.php
```

## License

MIT
