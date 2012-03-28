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
 * @package    Pop_Dom
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dom;

use Pop\Http\Response;

/**
 * This is the Dom class for the Dom component.
 *
 * @category   Pop
 * @package    Pop_Dom
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0
 */
class Dom extends AbstractDom
{

    /**
     * Constant to use the HTML trans doctype
     * @var int
     */
    const HTML_TRANS = 0;

    /**
     * Constant to use HTML strict doctype
     * @var int
     */
    const HTML_STRICT = 1;

    /**
     * Constant to use the HTML frames doctype
     * @var int
     */
    const HTML_FRAMES = 2;

    /**
     * Constant to use the XHTML trans doctype
     * @var int
     */
    const XHTML_TRANS = 3;

    /**
     * Constant to use the XHTML strict doctype
     * @var int
     */
    const XHTML_STRICT = 4;

    /**
     * Constant to use the XHTML frames doctype
     * @var int
     */
    const XHTML_FRAMES = 5;

    /**
     * Constant to use the XHTML 1.1 doctype
     * @var int
     */
    const XHTML11 = 6;

    /**
     * Constant to use the XML doctype
     * @var int
     */
    const XML = 7;

    /**
     * Constant to use the HTML5 doctype
     * @var int
     */
    const HTML5 = 8;

    /**
     * Constant to use the RSS doctype
     * @var int
     */
    const RSS = 9;

    /**
     * Constant to use the ATOM doctype
     * @var int
     */
    const ATOM = 10;

    /**
     * Document type
     * @var string
     */
    protected $doctype = 7;

    /**
     * Document content type
     * @var string
     */
    protected $contentType = 'application/xml';

    /**
     * Document charset
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Document doctypes
     * @var array
     */
    protected static $doctypes = array(
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n",
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n",
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n",
        "<!DOCTYPE html>\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n"
    );

    /**
     * Constructor
     *
     * Instantiate the document object
     *
     * @param  string $doctype
     * @param  string $charset
     * @param  array|Pop\Dom\Child $childNode
     * @param  string $indent
     * @return void
     */
    public function __construct($doctype = null, $charset = 'utf-8', $childNode = null, $indent = null)
    {
        $this->setDoctype($doctype);
        $this->charset = $charset;
        $this->indent = $indent;

        if (null !== $childNode) {
            $this->addChild($childNode);
        }
    }

    /**
     * Method to return the document type.
     *
     * @return string
     */
    public function getDoctype()
    {
        return str_replace('[{charset}]', $this->charset, Dom::$doctypes[$this->doctype]);
    }

    /**
     * Method to return the document charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Method to return the document charset.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Method to set the document type.
     *
     * @param  string $doctype
     * @return Pop\Dom\Dom
     */
    public function setDoctype($doctype = null)
    {
        if (null !== $doctype) {
            $doctype = (int)$doctype;

            if (array_key_exists($doctype, Dom::$doctypes)) {
                $this->doctype = $doctype;
                switch ($this->doctype) {
                    case Dom::ATOM:
                        $this->contentType = 'application/atom+xml';
                        break;
                    case Dom::RSS:
                        $this->contentType = 'application/rss+xml';
                        break;
                    case Dom::XML:
                        $this->contentType = 'application/xml';
                        break;
                    default:
                        $this->contentType = 'text/html';
                }
            }
        } else {
            $this->doctype = null;
        }

        return $this;
    }

    /**
     * Method to set the document charset.
     *
     * @param  string $chr
     * @return Pop\Dom\Dom
     */
    public function setCharset($chr)
    {
        $this->charset = $chr;
        return $this;
    }

    /**
     * Method to set the document charset.
     *
     * @param  string $content
     * @return Pop\Dom\Dom
     */
    public function setContentType($content)
    {
        $this->contentType = $content;
        return $this;
    }

    /**
     * Method to render the document and its child elements.
     *
     * @param  boolean $ret
     * @return void
     */
    public function render($ret = false)
    {
        // If the return flag is passed, return output.
        if ($ret) {
            $this->output = '';
            if (null !== $this->doctype) {
                $this->output .= str_replace('[{charset}]', $this->charset, Dom::$doctypes[$this->doctype]);
            }
            foreach ($this->childNodes as $child) {
                $this->output .= $child->render(true, 0, $this->indent);
            }
            return $this->output;
        // Else, print output.
        } else {
            if (null !== $this->doctype) {
                if (!headers_sent()) {
                    $response = new Response(200, array('Content-type' => $this->contentType));
                    $response->sendHeaders();
                }
                echo str_replace('[{charset}]', $this->charset, Dom::$doctypes[$this->doctype]);
            }

            foreach ($this->childNodes as $child) {
                $child->render(false, 0, $this->indent);
            }
        }
    }

}
