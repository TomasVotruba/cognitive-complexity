<?php

function arrowFunction(array $items) {
    return array_map(fn ($item) => $item ? 1 : 2, $items); // +0 (but nesting level is now 1), ternary +2 (nesting=1)
}
