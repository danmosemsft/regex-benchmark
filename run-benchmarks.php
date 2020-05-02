<?php

const PATTERNS_COUNT = 3;

const RUN_TIMES = 10;

const BUILDS = [
   'C# .Net Core 50' => 'dotnet-sdk.dotnet build csharp/benchmark.csproj -c Release',
 
];

const COMMANDS = [
   
    'C# .Net Core 50' => 'dotnet-sdk.dotnet csharp/bin/Release/netcoreapp5.0/benchmark.dll',
   
];

echo '- Build' . PHP_EOL;

foreach (BUILDS as $language => $buildCmd) {
    shell_exec($buildCmd);

    echo $language . ' built.' . PHP_EOL;
}

echo PHP_EOL . '- Run' . PHP_EOL;

$results = [];

foreach (COMMANDS as $language => $command) {
    echo $language . ' running.';

    $currentResults = [];

    for ($i = 0; $i < RUN_TIMES; $i++) {
        $out = shell_exec($command . ' input-text.txt');
        preg_match_all('/^\d+\.\d+/m', $out, $matches);

        if (sizeof($matches[0]) === 0) {
            break;
        }

        for ($j = 0; $j < PATTERNS_COUNT; $j++) {
            $currentResults[$j][] = $matches[0][$j];
        }
        echo $out;
        echo '.';
    }

    if (sizeof($currentResults) !== 0) {
        for ($i = 0; $i < PATTERNS_COUNT; $i++) {
            $results[$language][] = array_sum($currentResults[$i]) / count($currentResults[$i]);
        }

        $results[$language][PATTERNS_COUNT] = array_sum($results[$language]);
    }

    echo $language . ' ran.' . PHP_EOL;
}

echo PHP_EOL . '- Results' . PHP_EOL;

uasort($results, function ($a, $b) {
    return $a[PATTERNS_COUNT] < $b[PATTERNS_COUNT] ? -1 : 1;
});

$results = array_walk($results, function ($result, $language) {
    $result = array_map(function ($time) {
        return number_format($time, 2, '.', '');
    }, $result);

    echo '**' . $language . '** | ' . implode(' | ', $result) . PHP_EOL;
});
