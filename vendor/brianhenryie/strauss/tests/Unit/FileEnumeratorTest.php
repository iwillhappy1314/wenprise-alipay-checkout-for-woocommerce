<?php

// Verify there are no // double slashes in paths.

// exclude_from_classmap

// exclude regex

// paths outside project directory

namespace BrianHenryIE\Strauss\Tests\Unit;

use BrianHenryIE\Strauss\TestCase;

/**
 * Class FileEnumeratorTest
 * @package BrianHenryIE\Strauss\Tests\Unit
 * @coversDefaultClass \BrianHenryIE\Strauss\FileEnumerator
 */
class FileEnumeratorTest extends TestCase
{

    public function testNothing()
    {
        // this is to silence the "No tests found in class" warning.
        self::assertTrue(true);
    }
}
