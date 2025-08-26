<?php
/**
 * Get all built-in PHP classes, interfaces, traits.
 */

$outputFile = __DIR__ . '/builtins.php';

$builtins = file_exists($outputFile) ? require $outputFile : [];

$currentPhpVersion = implode(
    '.',
    array_slice(
        explode('.', phpversion()),
        0,
        2
    )
);

if (!isset($builtins[$currentPhpVersion])) {
    $builtins[$currentPhpVersion] = [
        'classes' => [],
        'interfaces' => [],
        'traits' => [],
    ];
}

$classes = array_filter(
    get_declared_classes(),
    function (string $className): bool {
        $reflector = new \ReflectionClass($className);
        return empty($reflector->getFileName());
    }
);

$interfaces = array_filter(
    get_declared_interfaces(),
    function (string $interfaceName): bool {
        $reflector = new \ReflectionClass($interfaceName);
        return empty($reflector->getFileName());
    }
);

$traits = array_filter(
    get_declared_traits(),
    function (string $traitName): bool {
        $reflector = new \ReflectionClass($traitName);
        return empty($reflector->getFileName());
    }
);


// Remove classes, interfaces, traits that are built-in in this PHP version from future versions.
foreach ($builtins as $phpVersion => $builtinsArray) {
    if (version_compare($phpVersion, $currentPhpVersion, '>')) {
        $builtins[$phpVersion]['classes'] = array_diff($builtinsArray['classes'], $classes);
        $builtins[$phpVersion]['interfaces'] = array_diff($builtinsArray['interfaces'], $interfaces);
        $builtins[$phpVersion]['traits'] = array_diff($builtinsArray['traits'], $traits);
    }
}

// Remove from this PHP version's built-ins list classes, interfaces, traits that exist in older versions.
foreach ($builtins as $phpVersion => $builtinsArray) {
    if (version_compare($phpVersion, $currentPhpVersion, '<')) {
        $classes = array_diff($classes, $builtinsArray['classes']);
        $interfaces = array_diff($interfaces, $builtinsArray['interfaces']);
        $traits = array_diff($traits, $builtinsArray['traits']);
    }
}

$builtins[$currentPhpVersion]['classes'] = array_unique(array_merge($builtins[$currentPhpVersion]['classes'], $classes));
$builtins[$currentPhpVersion]['interfaces'] = array_unique(array_merge($builtins[$currentPhpVersion]['interfaces'], $interfaces));
$builtins[$currentPhpVersion]['traits'] = array_unique(array_merge($builtins[$currentPhpVersion]['traits'], $traits));

foreach ($builtins as $phpVersion => $builtinsArray) {
    asort($builtins[$currentPhpVersion]['classes']);
    asort($builtins[$currentPhpVersion]['interfaces']);
    asort($builtins[$currentPhpVersion]['traits']);
}


$outputText = '<?php' . PHP_EOL . 'return ' . var_export($builtins, true) . ';';

$outputText = preg_replace('/\d+\s=>\s/', '', $outputText);

file_put_contents($outputFile, $outputText);
