<?php
/**
 * @see https://github.com/coenjacobs/mozart/issues/90
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * Class MozartIssue90Test
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue90Test extends IntegrationTestCase
{

    /**
     * Issue 90. Needs "iio/libmergepdf".
     *
     * Error: "File already exists at path: classmap_directory/tecnickcom/tcpdf/tcpdf.php".
     */
    public function testLibpdfmergeSucceeds()
    {

        $composerJsonString = <<<'EOD'
{
	"name": "brianhenryie/mozart-issue-90",
	"require": {
		"iio/libmergepdf": "4.0.4"
	},
	"extra": {
		"strauss": {
			"namespace_prefix": "BrianHenryIE\\Strauss\\",
			"classmap_prefix": "BrianHenryIE_Strauss_"
		}
	}
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $result = $this->runStrauss();

        self::assertEqualsRN(0, $result);

        // This test would only fail on Windows?
        self::assertDirectoryDoesNotExist($this->testsWorkingDir .'strauss/iio/libmergepdf/vendor/iio/libmergepdf/tcpdi');

        self::assertFileExists($this->testsWorkingDir .'vendor-prefixed/iio/libmergepdf/tcpdi/tcpdi.php');
    }
}
