<?php
/**
 * @see https://github.com/BrianHenryIE/strauss/issues/8
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue8Test extends IntegrationTestCase
{

    /**
     * @author BrianHenryIE
     */
    public function test_delete_vendor_files()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "brianhenryie/strauss-issue-8",
  "require": {
    "htmlburger/carbon-fields": "*"
  },
  "extra": {
    "strauss":{
      "delete_vendor_files": true
    }
  }
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $result = $this->runStrauss();

        self::assertEqualsRN(0, $result);

        self::assertFileDoesNotExist($this->testsWorkingDir. 'vendor/htmlburger/carbon-fields/core/Carbon_Fields.php');
    }
}
