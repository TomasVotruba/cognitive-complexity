# Keep Cognitive Complexity Down

<br>

Cognitive complexity tells us, how difficult code is to understand by a reader.

**How is cognitive complexity measured?**

```php
function get_words_from_number(int $number): string
{
    $amountInWords = '';

    if ($number === 1) {            // + 1
        $amountInWords = 'one';
    } elseif ($number === 2) {      // + 1
        $amountInWords = 'couple';
    } elseif ($number === 3) {      // + 1
        $amountInWords = 'a few';
    } else {                        // + 1
        $amountInWords = 'a lot';
    }

    return $amountInWords;
}
```

This function uses nesting, conditions and continue back and forth. It's hard to read and results in **cognitive complexity of 4**.

How to keep **cognitive complexity on 1**? Read [Keep Cognitive Complexity Low with PHPStan](https://tomasvotruba.com/blog/keep-cognitive-complexity-low-with-phpstan/) post to learn it.

<br>

## Install

```bash
composer require tomasvotruba/cognitive-complexity --dev
```

The package is available on PHP 7.2-8.1 versions in tagged releases.

<br>

## Usage

With [PHPStan extension installer](https://github.com/phpstan/extension-installer), everything is ready to run.

Enable each item on their own with simple configuration:

```neon
# phpstan.neon
parameters:
    cognitive_complexity:
        class: 50
        function: 8
```
