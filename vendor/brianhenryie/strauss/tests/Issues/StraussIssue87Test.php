<?php
/**
 * Strauss does not remove namespaced classes from the composer classmap files when optimize-autoloader is enabled
 *
 * @see https://github.com/BrianHenryIE/strauss/issues/87
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue87Test extends IntegrationTestCase
{
    public function test_autoload_classmap()
    {

        $composerJsonString = <<<'EOD'
{
	"require": {
		"psr/container": "^1.1"
	},
	"extra": {
		"strauss": {
			"target_directory": "vendor-prefixed",
			"classmap_prefix": "Class_Prefix_",
			"constant_prefix": "Constant_",
			"namespace_prefix": "New\\Namespace",
			"delete_vendor_files": true,
			"packages": [
				"psr/container"
			]
		}
	}
}
EOD;

        chdir($this->testsWorkingDir);

        file_put_contents($this->testsWorkingDir . '/composer.json', $composerJsonString);

        exec('composer install');
        exec('composer dump-autoload --optimize');

        $autoload_classmap_php_string = file_get_contents($this->testsWorkingDir . '/vendor/composer/autoload_classmap.php');
        self::assertStringContainsString("'Psr\\\\Container\\\\ContainerExceptionInterface' => \$vendorDir . '/psr/container/src/ContainerExceptionInterface.php',", $autoload_classmap_php_string);

        $result = $this->runStrauss();

        exec('composer dump-autoload');

        $autoload_classmap_php_string = file_get_contents($this->testsWorkingDir . '/vendor/composer/autoload_classmap.php');
        self::assertStringNotContainsString("'Psr\\\\Container\\\\ContainerExceptionInterface' => \$vendorDir . '/psr/container/src/ContainerExceptionInterface.php',", $autoload_classmap_php_string);
    }
}
