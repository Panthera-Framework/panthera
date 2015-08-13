<?php
namespace Panthera;

/**
 * Panthera Framework 2 localization class
 *
 *
 *
 * @package Panthera
 * @author Damian Kęska <webnull.www@gmail.com>
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class locale extends baseClass
{
    /**
     * Current active language
     *
     * @var string
     */
    public $activeLanguage = 'original';

    public $translationStrings = array(

    );

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
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     * @throws SyntaxException
     * 
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return bool
     */
    public function loadTranslationDomain($domain)
    {
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
     * @throws SyntaxException
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return array
     */
    public function compileCSV($body)
    {
        $lines = explode("\n", $body);
        $translations = array();
        $newLinesArray = array();
        $newLine = 0;
        $count = count($lines);

        /** Join multiline translations */
        foreach ($lines as $number => $line)
        {
            //if (!isset($lines[$number])) continue;
            $occurrences = substr_count($line, '"');

            if (!$occurrences || $occurrences % 2)
            {
                if ($number === $count)
                {
                    throw new SyntaxException('Syntax error in parsed CSV file, ending entry does not have a pair of double quotes present', 'FW_LOCALE_CSV_QUOTES');
                }

                if (!isset($newLinesArray[$newLine]))
                {
                    $newLinesArray[$newLine] = '';
                } else {
                    $newLinesArray[$newLine] .= "\n";
                }

                $newLinesArray[$newLine] .= $line;
            } else {
                $newLine++;
                $newLinesArray[$newLine] = $line;
            }
        }

        /** Finally parse CSV file using built-in function */
        foreach ($newLinesArray as &$line)
        {
            $line = str_getcsv($line);
            $translations[$line[0]] = $line[1];
        }

        return $translations;
    }
}