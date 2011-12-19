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
 * @package    Pop_Code
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code;

/**
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Reflection extends \ReflectionClass
{

    /**
     * Code to reflect
     * @var string
     */
    protected $_code = null;

    /**
     * Code generator object
     * @var Pop\Code\Generator
     */
    protected $_generator = null;

    /**
     * Constructor
     *
     * Instantiate the code reflection object
     *
     * @param  string  $code
     * @return void
     */
    public function __construct($code)
    {
        $this->_code = $code;
        parent::__construct($code);
        $this->_buildGenerator();
    }

    /**
     * Static method to instantiate the code reflection object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string  $code
     * @return Pop\Code\Reflection
     */
    public static function factory($code)
    {
        return new self($code);
    }

    /**
     * Get the code string
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get the code generator
     *
     * @return Pop\Code\Generator
     */
    public function getGenerator()
    {
        return $this->_generator;
    }

    /**
     * Build the code generator based the reflection class
     *
     * @return void
     */
    protected function _buildGenerator()
    {

        // Create generator object
        $type = ($this->isInterface()) ? Generator::CREATE_INTERFACE : Generator::CREATE_CLASS;
        $this->_generator = new Generator($this->getShortName() . '.php', $type);

        // Get the namespace
        $this->_getNamespace();

        // Detect and set the class docblock
        $classDocBlock = $this->getDocComment();
        if (!empty($classDocBlock)) {
            $this->_generator->code()->setDocblock(DocblockGenerator::parse($classDocBlock));
        }

        // Detect and set if the class is abstract
        if (!$this->isInterface() && $this->isAbstract()) {
            $this->_generator->code()->setAbstract(true);
        }

        // Detect and set if the class is a child class
        $parent = $this->getParentClass();
        if ($parent !== false) {
            if ($parent->inNamespace()) {
                $this->_generator->getNamespace()->setUse($parent->getNamespaceName() . '\\' . $parent->getShortName());
            }
            $this->_generator->code()->setParent($parent->getShortName());
        }

        // Detect and set if the class implements any interfaces
        if (!$this->isInterface()) {
            $interfaces = $this->getInterfaces();
            if ($interfaces !== false) {
                $interfacesAry = array();
                foreach ($interfaces as $interface) {
                    if ($interface->inNamespace()) {
                        $this->_generator->getNamespace()->setUse($interface->getNamespaceName() . '\\' . $interface->getShortName());
                    }
                    $interfacesAry[] = $interface->getShortName();
                }
                $this->_generator->code()->setInterface(implode(', ', $interfacesAry));
            }
        }

        // Detect and set constants
        $constants = $this->getConstants();
        if (count($constants) > 0) {
            foreach ($constants as $key => $value) {
                $this->_generator->code()->addProperty(new PropertyGenerator($key, gettype($value), $value, 'const'));
            }
        }

        // Get properties
        $this->_getProperties();

        // Get Methods
        $this->_getMethods();
    }

    /**
     * Get the namespace and uses, if any
     *
     * @return void
     */
    protected function _getNamespace()
    {
        $fileContents = (file_exists($this->getFilename())) ? file_get_contents($this->getFilename()) : null;

        // Detect and set namespace
        if ($this->inNamespace()) {
            $this->_generator->setNamespace(new NamespaceGenerator($this->getNamespaceName()));
            if (null !== $fileContents) {
                $matches = array();
                preg_match('/^use(.*)/m', $fileContents, $matches, PREG_OFFSET_CAPTURE);
                if (isset($matches[0][0])) {
                    $uses = substr($fileContents, $matches[0][1] + 4);
                    $uses = substr($uses, 0, strpos($uses, ';'));
                    $usesAry = explode(',', $uses);
                    foreach ($usesAry as $use) {
                        $use = trim($use);
                        $as = null;
                        if (stripos($use, 'as') !== false) {
                            $as = trim(substr($use, (strpos($use, 'as') + 2)));
                            $use = trim(substr($use, 0, strpos($use, 'as')));
                        }
                        $this->_generator->getNamespace()->setUse($use, $as);
                    }
                }
            }
        }
    }

    /**
     * Get properties
     *
     * @return void
     */
    protected function _getProperties()
    {
        // Detect and set properties
        $properties = $this->getDefaultProperties();

        if (count($properties) > 0) {
            foreach ($properties as $name => $value) {
                $property = $this->getProperty($name);
                if ($property->isPublic()) {
                    $visibility = 'public';
                } else if ($property->isProtected()) {
                    $visibility = 'protected';
                } else if ($property->isPrivate()) {
                    $visibility = 'private';
                }

                $doc = $property->getDocComment();
                if (null !== $doc) {
                    $docblock = DocblockGenerator::parse($doc);
                    $desc = $docblock->getDesc();
                    $type = $docblock->getTag('var');
                } else {
                    $type = strtolower(gettype($value));
                    $desc = null;
                }

                if (is_array($value)) {
                    $formattedValue = (count($value) == 0) ? null : $value;
                } else {
                    $formattedValue = $value;
                }
                $class = $this->getName();
                $prop = new PropertyGenerator($property->getName(), $type, $formattedValue, $visibility);
                $prop->setStatic($property->isStatic());
                $prop->setDesc($desc);
                $this->_generator->code()->addProperty($prop);
            }
        }
    }

    /**
     * Get methods
     *
     * @return void
     */
    protected function _getMethods()
    {
        // Detect and set methods
        $methods = $this->getMethods();

        if (count($methods) > 0) {
            foreach ($methods as $value) {
                $methodExport = \ReflectionMethod::export($value->class, $value->name, true);

                // Get the method docblock
                if ((strpos($methodExport, '/*') !== false) && (strpos($methodExport, '*/') !== false)) {
                    $docBlock = substr($methodExport, strpos($methodExport, '/*'));
                    $docBlock = substr($docBlock, 0, (strpos($methodExport, '*/') + 2));
                } else {
                    $docBlock = null;
                }

                $method = $this->getMethod($value->name);

                if ($method->isPublic()) {
                    $visibility = 'public';
                } else if ($method->isProtected()) {
                    $visibility = 'protected';
                } else if ($method->isPrivate()) {
                    $visibility = 'private';
                }

                $mthd = new MethodGenerator($value->name, $visibility, $method->isStatic());
                if (null !== $docBlock) {
                    $mthd->setDocblock(DocblockGenerator::parse($docBlock, $mthd->getIndent()));
                }
                $mthd->setFinal($method->isFinal())
                     ->setAbstract($method->isAbstract());

                // Get the method parameters
                if (stripos($methodExport, 'Parameter') !== false) {
                    $matches = array();
                    preg_match_all('/Parameter \#(.*)\]/m', $methodExport, $matches);
                    if (isset($matches[0][0])) {
                        foreach ($matches[0] as $param) {
                            $name = null;
                            $value = null;
                            $type = null;

                            // Get name
                            $name = substr($param, strpos($param, '$'));
                            $name = trim(substr($name, 0, strpos($name, ' ')));

                            // Get value
                            if (strpos($param, '=') !== false) {
                                $value = trim(substr($param, (strpos($param, '=') + 1)));
                                $value = trim(substr($value, 0, strpos($value, ' ')));
                                $value = str_replace('NULL', 'null', $value);
                            }

                            // Get type
                            $type = substr($param, (strpos($param, '>') + 1));
                            $type = trim(substr($type, 0, strpos($type, '$')));
                            if ($type == '') {
                                $type = null;
                            }

                            $mthd->addArgument($name, $value, $type);
                        }
                    }
                }

                // Get method body
                if ((strpos($methodExport, '@@') !== false) && (file_exists($this->getFilename()))) {
                    $lineNums = substr($methodExport, (strpos($methodExport, '@@ ') + 3));
                    $lineNums = substr($lineNums, (strpos($lineNums, ' ') + 1));
                    $lineNums = trim(substr($lineNums, 0, strpos($lineNums, PHP_EOL)));
                    $lineNumsAry = explode(' - ', $lineNums);
                    if (isset($lineNumsAry[0]) && isset($lineNumsAry[1])) {
                        $classLines = file($this->getFilename());
                        $body = null;
                        $start = $lineNumsAry[0] + 1;
                        $end = $lineNumsAry[1] - 1;
                        for ($i = $start; $i < $end; $i++) {
                            if (substr($classLines[$i], 0, 8) == '        ') {
                                $body .= substr($classLines[$i], 8);
                            } else if (substr($classLines[$i], 0, 4) == '    ') {
                                $body .= substr($classLines[$i], 4);
                            } else if (substr($classLines[$i], 0, 2) == "\t\t") {
                                $body .= substr($classLines[$i], 2);
                            } else if (substr($classLines[$i], 0, 1) == "\t") {
                                $body .= substr($classLines[$i], 1);
                            } else {
                                $body .= $classLines[$i];
                            }
                        }
                        $mthd->setBody(rtrim($body));
                    }
                }

                $this->_generator->code()->addMethod($mthd);
            }
        }
    }

}