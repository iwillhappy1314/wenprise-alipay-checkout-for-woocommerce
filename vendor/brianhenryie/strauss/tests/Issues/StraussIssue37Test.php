<?php
/**
 *
 * @see https://github.com/BrianHenryIE/strauss/issues/37
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue37Test extends IntegrationTestCase
{

    /**
     */
    public function test_can_handle_psr_namespace_with_path_array()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "brianhenryie/strauss-psr-4-path-array",
  "minimum-stability": "dev",
  "require": {
    "automattic/woocommerce": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "BrianHenryIE\\Strauss\\",
      "classmap_prefix": "BH_Strauss_"
    }
  }
}

EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $result = $this->runStrauss();

        self::assertNotEquals(1, $result);
    }
}
