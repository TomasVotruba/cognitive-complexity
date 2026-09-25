<?php

function mixedOperators($a, $b, $c, $d) {
    if ($a && $b || $c) { // +1, && +1, || +1
    }

    return $c or $d; // +1
}
