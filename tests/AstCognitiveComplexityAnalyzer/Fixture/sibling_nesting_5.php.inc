<?php

function siblings(array $items) {
    foreach ($items as $item) { // +1
        if ($item) { // +2 (nesting=1)
        }

        if ($item) { // +2 (nesting=1)
        }
    }
}
