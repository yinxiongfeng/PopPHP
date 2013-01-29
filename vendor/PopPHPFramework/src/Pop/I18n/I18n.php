<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2013 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\I18n;

/**
 * I18n exception class
 *
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2013 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class I18n
{

    /**
     * Default system language
     * @var string
     */
    protected $language = null;

    /**
     * Default system locale
     * @var string
     */
    protected $locale = null;

    /**
     * Language content
     * @var array
     */
    protected $content = array(
        'source' => array(),
        'output' => array()
    );

    /**
     * Constructor
     *
     * Instantiate the I18n object.
     *
     * @param  string $lang
     * @return \Pop\I18n\I18n
     */
    public function __construct($lang = null)
    {
        if (null === $lang) {
            $lang = (defined('POP_LANG')) ? POP_LANG : 'en-us';
        }

        if (strpos($lang, '-') !== false) {
            $ary  = explode('-', $lang);
            $this->language = $ary[0];
            $this->locale = $ary[1];
        } else {
            $this->language = $lang;
            $this->locale = $lang;
        }

        $this->loadCurrentLanguage();
    }

    /**
     * Static method to load the I18n object.
     *
     * @param  string $lang
     * @return \Pop\I18n\I18n
     */
    public static function factory($lang = null)
    {
        return new self($lang);
    }

    /**
     * Get current language setting.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get current locale setting.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Load language content from an XML file.
     *
     * @param  string $langFile
     * @throws Exception
     * @return void
     */
    public function loadFile($langFile)
    {
        if (file_exists($langFile)) {
            if (($xml =@ new \SimpleXMLElement($langFile, LIBXML_NOWARNING, true)) !== false) {
                $key = 0;
                $length = count($xml->locale);

                // Find the locale node key
                for ($i = 0; $i < $length; $i++) {
                    if ($this->locale == (string)$xml->locale[$i]->attributes()->region) {
                        $key = $i;
                    }
                }

                // If the locale node matches the current locale
                if ($this->locale == (string)$xml->locale[$key]->attributes()->region) {
                    foreach ($xml->locale[$key]->text as $text) {
                        if (isset($text->source) && isset($text->output)) {
                            $this->content['source'][] = (string)$text->source;
                            $this->content['output'][] = (string)$text->output;
                        }
                    }
                }
            } else {
                throw new Exception('Error: There was an error processing that XML file.');
            }
        } else {
            throw new Exception('Error: The language file ' . $langFile . ' does not exist.');
        }
    }

    /**
     * Return the translated string
     *
     * @param  string $str
     * @param  string|array $params
     * @return $str
     */
    public function __($str, $params = null)
    {
        return $this->translate($str, $params);
    }

    /**
     * Echo the translated string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return void
     */
    public function _e($str, $params = null)
    {
        echo $this->translate($str, $params);
    }

    /**
     * Get languages from the XML files.
     *
     * @param  string $dir
     * @return array
     */
    public static function getLanguages($dir = null)
    {
        $langsAry = array();
        $langDirectory = (null !== $dir) ? $dir : __DIR__ . '/Data';

        if (file_exists($langDirectory)) {
            $langDir = new \Pop\File\Dir($langDirectory);
            $files = $langDir->getFiles();
            foreach ($files as $file) {
                if ($file != '__.xml') {
                    if (($xml =@ new \SimpleXMLElement($langDirectory . '/' . $file, LIBXML_NOWARNING, true)) !== false) {
                        $lang = (string)$xml->attributes()->output;
                        $langName = (string)$xml->attributes()->name;
                        $langNative = (string)$xml->attributes()->native;

                        foreach ($xml->locale as $locale) {
                            $region = (string)$locale->attributes()->region;
                            $name   = (string)$locale->attributes()->name;
                            $native = (string)$locale->attributes()->native;

                            if ($name != $native) {
                                if ($langName != $name) {
                                    $native .= ' (' . $langName . ', ' . $name . ')';
                                } else {
                                    $native .= ' (' . $name . ')';
                                }
                            }

                            if ($region == $lang) {
                                $langsAry[$lang] = $native;
                            } else {
                                if ($langNative != (string)$locale->attributes()->native) {
                                    $langsAry[$lang . '-' . $region] = $langNative . ', ' . $native;
                                } else {
                                    $langsAry[$lang . '-' . $region] = $native;
                                }
                            }
                        }
                    }
                }
            }
        }

        ksort($langsAry);
        return $langsAry;
    }

    /**
     * Translate and return the string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return mixed
     */
    protected function translate($str, $params = null)
    {
        $key = array_search($str, $this->content['source']);
        $trans = ($key !== false) ? $this->content['output'][$key] : $str;

        if (null !== $params) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $trans = str_replace('%' . ($key + 1), $value, $trans);
                }
            } else {
                $trans = str_replace('%1', $params, $trans);
            }
        }

        return $trans;
    }

    /**
     * Get language content from the XML file.
     *
     * @return void
     */
    protected function loadCurrentLanguage()
    {
        $this->loadFile(__DIR__ . '/Data/' . $this->language . '.xml');
    }

}