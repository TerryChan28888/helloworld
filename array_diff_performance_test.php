<?php

// https://gist.github.com/nunoveloso/1992851
require_once "./nuno-array-diff.php";

const FUNCS = ["array_diff", "nuno_array_diff"];

// Config options
const ARRAY_SIZES_RANGE = [
    [100, 1000000],
    [100, 1000000],
];
const ARRAY_SIZE_FACTOR = 100;
const VALUE_MIN = 0;
const VALUE_MAX_RANGE = [
    10, 10000000
];
const VALUE_MAX_FACTOR = 100;
const ITERATION_COUNT = 7;
const STRING_MODE = false;

function main()
{
    printTableHeader();

    $arraySizes = [];
    for (
        $arraySizes[0] = ARRAY_SIZES_RANGE[0][0];
        $arraySizes[0] <= ARRAY_SIZES_RANGE[0][1];
        $arraySizes[0] *= ARRAY_SIZE_FACTOR
    ) {
        for (
            $arraySizes[1] = ARRAY_SIZES_RANGE[1][0];
            $arraySizes[1] <= ARRAY_SIZES_RANGE[1][1];
            $arraySizes[1] *= ARRAY_SIZE_FACTOR
        ) {
            for (
                $valueMax = VALUE_MAX_RANGE[0];
                $valueMax <= VALUE_MAX_RANGE[1];
                $valueMax *= VALUE_MAX_FACTOR
            ) {
                $valueRange = [
                    VALUE_MIN,
                    $valueMax,
                ];

                $times = measure($arraySizes, $valueRange);
                printTimesAsTableContent($arraySizes, $valueRange, $times);
            }
        }
    }
}

function measure(array $arraySizes, array $valueRange): array
{
    $arrays = [
        array_fill(0, $arraySizes[0], 0),
        array_fill(0, $arraySizes[1], 0),
    ];
    $times = [0.0, 0.0];

    for ($k = 0; $k < ITERATION_COUNT; $k++) {
        foreach ($arrays as &$arr) {
            foreach ($arr as &$item) {
                $item = getRandomValue($valueRange);
            }
        }

        foreach (FUNCS as $i => $func) {
            $startTime = microtime(true);
            $result = ($func)($arrays[0], $arrays[1]);
            $times[$i] += microtime(true) - $startTime;
        }
    }

    return $times;
}

function getRandomValue(array $valueRange)
{
    $randomInt = random_int(
        $valueRange[0],
        $valueRange[1],
    );

    if (!STRING_MODE) {
        return $randomInt;
    }
    return base64_encode(random_bytes($randomInt));
}

function printTableHeader(): void
{
    $headerCells = [
        "Array Sizes (#1, #2)",
        STRING_MODE ? "String Length Range(s)" : "Value Range(s)",
        "Time #1",
        "Time #2",
        "#2 is Faster than #1 by",
    ];

    echo
        implode("|", $headerCells), PHP_EOL,
        "|", str_repeat("-|", count($headerCells)), PHP_EOL;
}

function printTimesAsTableContent(
    array $arraySizes,
    array $valueRange,
    array $times
): void {
    $times = array_map(
        fn($time) => round($time / ITERATION_COUNT, 4),
        $times
    );

    echo implode("|", [
        "(" . $arraySizes[0] . ", " . $arraySizes[1] . ")",
        "[" . $valueRange[0] . ", " . $valueRange[1] . "]",

        str_pad($times[0], 6, "0", STR_PAD_RIGHT) . "s",
        str_pad($times[1], 6, "0", STR_PAD_RIGHT) . "s",

        $times[1] === 0.0 ? "-" : (
            (
                $times[0] >= $times[1] ?
                round(($times[0] / $times[1] - 1) * 100) :
                -round(($times[1] / $times[0] - 1) * 100)
            ) .
            "% (" . round($times[0] / $times[1], 1) . "x)"
        ),
    ]), PHP_EOL;
}

main();