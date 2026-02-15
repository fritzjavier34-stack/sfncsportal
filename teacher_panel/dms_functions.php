<?php

function compute_initial_grade($ww, $pt, $qa) {
    return round(
        ($ww * 0.40) +
        ($pt * 0.40) +
        ($qa * 0.20),
        2
    );
}

/*
  ⚠️ Replace this table with the EXACT
  DMS-Corrections.xlsm transmutation
*/
function transmute_grade($initial) {

    if ($initial >= 98) return 100;
    if ($initial >= 95) return 99;
    if ($initial >= 92) return 97;
    if ($initial >= 89) return 95;
    if ($initial >= 86) return 93;
    if ($initial >= 83) return 91;
    if ($initial >= 80) return 89;
    if ($initial >= 77) return 87;
    if ($initial >= 75) return 85;

    return 75;
}