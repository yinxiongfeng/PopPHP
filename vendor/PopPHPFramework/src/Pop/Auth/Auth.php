<?php
/**
 * Pop PHP Framework
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
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

use Pop\Auth\Adapter\AccessFile,
    Pop\Auth\Adapter\AdapterInterface,
    Pop\Auth\Adapter\DbTable,
    Pop\Auth\Rule\AllowedIps,
    Pop\Auth\Rule\Attempts,
    Pop\Auth\Rule\BlockedIps,
    Pop\Auth\Rule\RuleInterface,
    Pop\Locale\Locale;

/**
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Auth
{

    /**
     * Auth user object
     * @var Pop\Auth\User
     */
    protected $_user = null;

    /**
     * Array of Pop\Auth\Role objects
     * @var array
     */
    protected $_roles = array();

    /**
     * Array of Pop\Auth\Rule\* objects
     * @var array
     */
    protected $_rules = array();

    /**
     * Auth adapter object
     * @var mixed
     */
    protected $_adapter = null;

    /**
     * Constructor
     *
     * Instantiate the auth object
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Method to add a role
     *
     * @param  mixed $role
     * @return Pop\Auth\Auth
     */
    public function addRole(Role $role)
    {
        $this->_roles[$role->getName()] = $role;
        return $this;
    }

    /**
     * Method to add a rule
     *
     * @param  mixed $rule
     * @return Pop\Auth\Auth
     */
    public function addRule(RuleInterface $rule)
    {
        $this->_rules[] = $rule;
        return $this;
    }

}
