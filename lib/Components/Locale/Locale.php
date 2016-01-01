<?php
namespace Panthera\Components\Locale;
use Panthera\Classes\BaseExceptions\FileException;
use Panthera\Components\Kernel\BaseFrameworkClass;

/**
 * Panthera Framework 2 localization class
 *
 * @package Panthera\Components\Locale
 * @author Damian Kęska <webnull.www@gmail.com>
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class Locale extends BaseFrameworkClass
{
    /**
     * Current active language
     *
     * @var string
     */
    public $activeLanguage = 'original';

    /**
     * @var array
     */
    public $translationStrings = [];

    /**
     * Get translation value
     *
     * @param string $localeString Original text string
     * @param string $domain Domain name
     * @author Damian Kęska <webnull.www@gmail.com>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     * @return string
     */
    public function get($localeString, $domain)
    {
        // return original translation in case we are using a native language
        if ($this->activeLanguage == 'original')
        {
            return $localeString;
        }

        // default domain is "application", it could contain generic application-specific translations
        if (!$domain)
        {
            $domain = 'application';
        }

        if (!isset($this->translationStrings[$domain]))
        {
            $this->loadTranslationDomain($domain);
        }

        if (isset($this->translationStrings[$domain]) && isset($this->translationStrings[$domain][$localeString]))
        {
            return $this->translationStrings[$domain][$localeString];
        }

        return $localeString;
    }

    /**
     * Load a translation domain
     *
     * @param string $domain Domain name
     * @throws FileException
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return bool
     */
    public function loadTranslationDomain($domain)
    {
        $__list = [];
        $path = $this->app->getPath('translations/' .$this->activeLanguage. '/' .$domain. '.csv');
        $compiledPath = $this->app->appPath. '/.content/cache/translations/' .$this->activeLanguage. '/' .$domain. '.' .hash('md4', $path). '.php';

        // compile the file at first time
        if ($path && (!is_file($compiledPath) || filemtime($path) > filemtime($compiledPath)))
        {
            // verify permissions for read on source file
            if (!is_readable($path))
            {
                throw new FileException('File "' .$path. '" is not readable', 'FW_LOCALE_DOMAIN_NOT_READABLE');
            }

            // verify if language directory exists, so we could put domain here
            if (!is_dir($this->app->appPath. '/.content/cache/translations/' .$this->activeLanguage. '/'))
            {
                if (!is_writeable($this->app->appPath. '/.content/cache/translations/'))
                {
                    throw new FileException('Directory "' .$this->app->appPath. '/.content/cache/translations/" is not writable', 'FW_LOCALE_DOMAIN_NOT_WRITABLE');
                }

                mkdir($this->app->appPath. '/.content/cache/translations/' .$this->activeLanguage);
            }

            // check if we could write to a language directory
            if (!is_writable($this->app->appPath. '/.content/cache/translations/'))
            {
                throw new FileException('Directory "' .$this->app->appPath. '/.content/cache/translations/" is not writable', 'FW_LOCALE_DOMAIN_NOT_WRITABLE');
            }

            // run compilation
            $this->translationStrings[$domain] = $this->compileCSV(file_get_contents($path));

            // save to cache
            $fp = fopen($compiledPath, 'w');
            fwrite($fp, '<?php $__list = ' .var_export($this->translationStrings[$domain], true). ';');
            fclose($fp);

            if (function_exists('opcache_compile_file') && opcache_get_status()['opcache_enabled'])
            {
                opcache_compile_file($compiledPath);
            }
        }

        require $compiledPath;
        $this->translationStrings[$domain] = $__list;
        return true;
    }

    /**
     * Compile a CSV file
     *
     * - Supports multiline strings
     *
     * @param string $body Input text, CSV file content
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return array
     */
    public function compileCSV($body)
    {
        // escaping
        $body = str_replace('\"', '\@DOUBLEQUOTE_ESCAPE', $body);

        $tempTranslations = [];
        $len = strlen($body);
        $pos = 0;

        do
        {
            $pos = strpos($body, '"', $pos);

            if ($pos === false)
            {
                break;
            }

            $end = strpos($body, '",', $pos + 1);
            $key = substr($body, $pos + 1, ($end - $pos - 1));

            $translationStarts = strpos($body, '"', $end + 1);
            $translationEnds   = strpos($body, '"', $translationStarts + 1);
            $value = substr($body, $translationStarts + 1, ($translationEnds - $translationStarts - 1));

            $tempTranslations[$key] = $value;
            $pos = $translationEnds + 1;

        } while ($pos !== false && $pos < $len);

        $translations = [];

        foreach ($tempTranslations as $key => $value)
        {
            $translations[str_replace('\@DOUBLEQUOTE_ESCAPE', '\"', $key)] = str_replace('\@DOUBLEQUOTE_ESCAPE', '\"', $value);
        }

        return $translations;
    }
}