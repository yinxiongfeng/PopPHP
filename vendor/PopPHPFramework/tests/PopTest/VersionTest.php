<?php
/**
 * Pop PHP Framework Unit Tests
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 */

namespace PopTest;

use Pop\Loader\Autoloader,
    Pop\Version;

// Require the library's autoloader.
require_once __DIR__ . '/../../src/Pop/Loader/Autoloader.php';

// Call the autoloader's bootstrap function.
Autoloader::factory()->splAutoloadRegister();

class VersionTest extends \PHPUnit_Framework_TestCase
{

    public function testVersion()
    {
        $this->assertEquals('1.0.1', Version::getVersion());
        $this->assertEquals('1.0.1', trim(Version::getLatest()));
        $this->assertEquals(1, Version::compareVersion(1.1));
    }

    public function testCheck()
    {
        $results = Version::check();
        $results = Version::check(Version::HTML);
        $results = Version::check(Version::DATA);
        $this->assertGreaterThan(0, count($results));
    }

}

