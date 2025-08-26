<?php
/**
 * @see https://github.com/BrianHenryIE/strauss/issues/19
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue19Test extends IntegrationTestCase
{

    /**
     * Need to make the class finder in change enumerator stricter.
     *
     * @author BrianHenryIE
     */
    public function testObjectIsNotPrefixed()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "brianhenryie/strauss-issue-19",
  "require": {
    "iio/libmergepdf": "^4.0"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Strauss\\Issue19\\",
      "classmap_prefix": "Strauss_Issue19_"
    }
  }
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $result = $this->runStrauss();

        $php_string = file_get_contents($this->testsWorkingDir . 'vendor-prefixed/tecnickcom/tcpdf/include/tcpdf_static.php');

        self::assertStringNotContainsString('* Creates a copy of a class Strauss_Issue19_object', $php_string);
        
        self::assertStringContainsString('* Creates a copy of a class object', $php_string);
    }
}
