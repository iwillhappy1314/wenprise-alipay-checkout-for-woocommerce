<?php
/**
 * Undefined offset: 1
 *
 * @see https://github.com/BrianHenryIE/strauss/pull/91
 */

namespace BrianHenryIE\Strauss\Tests\Issues;

use BrianHenryIE\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * @package BrianHenryIE\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue91Test extends IntegrationTestCase
{
    public function test_namespace_keyword_on_opening_line()
    {
        $composerJsonString = <<<'EOD'
{
  "name": "pr/91",
  "require": {
    "phpoffice/phpspreadsheet": "1.29"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Company\\Project\\"
    }
  }
}
EOD;

        chdir($this->testsWorkingDir);

        file_put_contents($this->testsWorkingDir . '/composer.json', $composerJsonString);

        exec('composer install');

        $result = $this->runStrauss();

        self::assertEquals(0, $result);
    }
}
