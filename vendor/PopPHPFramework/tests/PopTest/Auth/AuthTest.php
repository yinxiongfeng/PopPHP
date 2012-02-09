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

namespace PopTest\Auth;

use Pop\Loader\Autoloader,
    Pop\Auth\Auth,
    Pop\Auth\Role,
    Pop\Auth\Adapter\AuthFile;

// Require the library's autoloader.
require_once __DIR__ . '/../../../src/Pop/Loader/Autoloader.php';

// Call the autoloader's bootstrap function.
Autoloader::factory()->splAutoloadRegister();

class AuthTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $a = new Auth(new AuthFile(__DIR__ . '/../tmp/access.txt'));
        $class = 'Pop\\Auth\\Auth';
        $this->assertTrue($a instanceof $class);
    }

    public function testIsValid()
    {
        $a = new Auth(new AuthFile(__DIR__ . '/../tmp/access.txt'));
        $a->authenticate('testuser1', '12test34');
        $this->assertTrue($a->isValid());
    }

    public function testIsAuthorized()
    {
        $a = new Auth(new AuthFile(__DIR__ . '/../tmp/access.txt'));
        $a->addRoles(Role::factory('admin', 3));
        $a->setRequiredRole('admin')
          ->authenticate('testuser1', '12test34');
        $this->assertTrue($a->isAuthorized());
    }

}

?>