<?php

function elseIfs(array $items) {
    foreach ($items as $item) { // +1
        if ($item === 1) { // +2 (nesting=1)
        } elseif ($item === 2) { // +1
        } else { // +1
        }
    }
}
