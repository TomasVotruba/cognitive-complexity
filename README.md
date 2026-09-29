# Keep Cognitive Complexity Down

<br>

Cognitive complexity tells how difficult code is for a reader to understand. This package adds PHPStan rules that report classes and methods that got too complex.

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

Every branch makes the reader keep one more path in their head. This function results in **cognitive complexity of 4**.

What adds complexity:

* **+1** for each `if`, `elseif`, `else`, `switch`, `match`, ternary, `for`, `foreach`, `while`, `do-while` and `catch`
* **+1** for each sequence of boolean operators, e.g. `$a && $b && $c` is +1, `$a && $b || $c` is +2
* **+1** for `goto` and `break`/`continue` with a level
* **+1 per nesting level** for structures nested in loops, conditions, closures or `catch`

How to keep **cognitive complexity on 1**? Read [Cognitive load is what matters](https://minds.md/zakirullin/cognitive) or [Sonar paper about cognitive complexity metrics](https://www.sonarsource.com/docs/CognitiveComplexity.pdf) that inspired this repository.

<img src="https://github.com/zakirullin/cognitive-load/blob/main/img/cognitiveloadv5paper.png?raw=true">


<br>

## Install

```bash
composer require tomasvotruba/cognitive-complexity --dev
```

The package runs on PHP 7.4+.

<br>

## Usage

With [PHPStan extension installer](https://github.com/phpstan/extension-installer), everything is ready to run. The class and function rules are enabled by default.

Adjust the limits in your config, these are the defaults:

```yaml
# phpstan.neon
parameters:
    cognitive_complexity:
        class: 40
        function: 9
```

Each rule has its own error identifier, so you can ignore it on a specific place:

* `complexity.classLike`
* `complexity.functionLike`

```yaml
# phpstan.neon
parameters:
    ignoreErrors:
        -
            identifier: complexity.functionLike
            path: src/SomeComplexClass.php
```

<br>

Happy coding!
