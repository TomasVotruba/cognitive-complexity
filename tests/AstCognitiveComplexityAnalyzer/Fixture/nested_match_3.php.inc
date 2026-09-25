<?php

function nestedMatch(array $items) {
    foreach ($items as $item) { // +1
        echo match ($item) { // +2 (nesting=1)
            1 => 'one',
            default => 'many',
        };
    }
}
