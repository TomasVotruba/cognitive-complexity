# Keep Cognitive Complexity Down

<br>

Cognitive complexity tells us, how difficult code is to understand by a reader.

**How is cognitive compleixty measured?**

```php
function sumOfPrimes($max) {
    $total = 0;
    for ($i = 1; $i < $max; ++$i) {     // +1
        for ($j = 2; $j < $i; ++$j) {   // +2
            if ($i % $j === 0) {        // +3
                continue 2;             // +1
            }
        }

        $total += $i;
    }

    return $total;
}
```

This function uses nesting, conditions and continue back and forth. It's hard to read and has complexity of 7.

How to keep it down and what else is included in measurements? Check [Is Your Code Readable By Humans?](https://tomasvotruba.com/blog/2018/05/21/is-your-code-readable-by-humans-cognitive-complexity-tells-you/) post to learn it.

<br>

## Install

```bash
composer require tomasvotruba/cognitive-complexity --dev
```

The package is available on PHP 7.2-8.1 versions in tagged releases.

<br>

## Usage

With PHPStan extension installer, everything is ready to run.

Enable each item on their own with simple configuration:

```neon
# phpstan.neon
parameters:
    cognitive_complexity:
        class: 50
        function: 8
```
