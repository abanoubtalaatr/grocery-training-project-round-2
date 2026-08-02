# PHP Memory Management — Practical Learning Guide

Hands-on demos for understanding how PHP manages memory: reference counting, object lifecycle, `unset()`, garbage collection, circular references, and Laravel `chunk()` optimization.

Branch: `garbage-collection`

---

## Quick start

```bash
# Pure PHP demos (no database required)
php memory-demos/run-all.php

# Or individually
php memory-demos/memory.php
php memory-demos/01-memory-basics.php
php memory-demos/02-reference-counting.php
php memory-demos/03-circular-references-gc.php
php memory-demos/04-large-memory.php
php memory-demos/05-tools-overview.php

# Laravel comparison (needs DB + migrations)
php artisan memory:test --seed=5000 --mode=compare --cleanup
```

---

## Reference Counting

PHP tracks how many references point to a value/object.

```
$user1 ----\
             User Object
$user2 ----/
```

Lifecycle:

```
Create object
Reference Count = 1

$user2 = $user1
Reference Count = 2

unset($user1)
Reference Count = 1

unset($user2)
Reference Count = 0
→ Object destroyed
→ Memory freed
```

When the count reaches zero, the destructor runs and memory can be reclaimed.

See: `memory-demos/02-reference-counting.php`

---

## Garbage Collection

### Problem: circular references

```
User <------> Order
```

After:

```php
unset($user);
unset($order);
```

No *external* references remain, but each object still references the other, so reference counts are not zero. Refcounting alone cannot free them.

### Solution

PHP’s cyclic garbage collector detects unreachable cycles and frees them.

```php
gc_status();
gc_collect_cycles();
```

See: `memory-demos/03-circular-references-gc.php`

---

## Object lifecycle diagram

```
┌─────────────┐     new User()      ┌──────────────────┐
│  Variable   │ ───────────────────►│  Object (zval)   │
│  $user1     │   refcount = 1      │  + __construct   │
└─────────────┘                     └──────────────────┘
       │                                      ▲
       │ $user2 = $user1                      │
       ▼                                      │
┌─────────────┐                               │
│  $user2     │ ──────────────────────────────┘
│             │   refcount = 2
└─────────────┘
       │
       │ unset($user1); unset($user2);
       ▼
  refcount = 0  →  __destruct()  →  memory freed
```

Circular case (needs GC):

```
┌──────┐  order   ┌───────┐
│ User │─────────►│ Order │
│      │◄─────────│       │
└──────┘   user   └───────┘
   ▲                   ▲
   │ unset both        │
   └─ refcounts > 0 ───┘  (cycle still alive)
            │
            ▼
   gc_collect_cycles() → both destroyed
```

---

## Laravel: `User::all()` vs `chunk()`

| Approach | Behavior | Risk |
|----------|----------|------|
| `User::all()` | Loads entire table into a collection | Memory grows with table size |
| `User::chunk(N, …)` | Loads N rows, processes, releases, repeats | Stable working set |

Bad:

```php
$users = User::all();
foreach ($users as $user) {
    // ...
}
```

Good:

```php
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        // ...
    }
});
```

Flow with chunking:

```
Load 1000 users → Process → Release memory
Load next 1000  → Process → Release memory
...
```

Artisan command:

```bash
php artisan memory:test --mode=all
php artisan memory:test --mode=chunk --chunk=500
php artisan memory:test --mode=compare --seed=10000 --cleanup
```

---

## Tools

### PHP built-in

- `memory_get_usage()` / `memory_get_peak_usage()`
- `gc_status()` / `gc_collect_cycles()`

### Xdebug (optional)

1. Install the extension (`pecl install xdebug` or package manager).
2. Enable `xdebug.mode=debug,develop`.
3. Break on demos or `MemoryTest` and inspect variables, objects, and stack.

### Laravel Debugbar (optional)

```bash
composer require barryvdh/laravel-debugbar --dev
```

In the browser (with `APP_DEBUG=true`): memory, queries, execution time.

---

## File map

| Path | Topic |
|------|--------|
| `memory-demos/memory.php` | Spec entry — basics |
| `memory-demos/01-memory-basics.php` | Allocation + `unset()` |
| `memory-demos/02-reference-counting.php` | Refcount + destructors |
| `memory-demos/03-circular-references-gc.php` | Cycles + GC |
| `memory-demos/04-large-memory.php` | Large arrays |
| `memory-demos/05-tools-overview.php` | Tooling checklist |
| `memory-demos/run-all.php` | Run every pure-PHP demo |
| `app/Console/Commands/MemoryTest.php` | Laravel `all` vs `chunk` |

---

## Learning checklist

- [ ] Watch memory change when assigning / unsetting variables
- [ ] See destructor fire only when refcount hits zero
- [ ] Observe circular refs surviving `unset()` until `gc_collect_cycles()`
- [ ] Measure a large array allocate and release
- [ ] Compare `User::all()` vs `User::chunk()` with `--seed` large enough to matter
