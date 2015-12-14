<?php
namespace Panthera\Components\Indexing;

use Panthera\Components\Kernel\Framework;
use Panthera\Components\Indexing\PhpParser\NodeVisitor_SignalSearcher;

/**
 * Allows indexing all places where signals are attached
 *
 * @package Panthera\deployment\build\framework\signals
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class SignalIndexing
{
    /**
     * Find all class methods that are registering or executing signals
     *
     * @todo Add validation, exceptions
     *
     * @param string $path Path to file
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public static function loadFile($path)
    {
        return static::loadString(file_get_contents($path), $path);
    }

    /**
     * Find registered signals and slots in PHP code
     *
     * @todo Add validation, exceptions
     *
     * @param string $code Input PHP code
      * @param string $file File the contents belongs to (optionally)
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return array
     */
    public static function loadString($code, $file = '')
    {
        $framework = Framework::getInstance();

        if ($file && is_file($file))
        {
            $file = realpath($file);
            $file = str_replace(PANTHERA_FRAMEWORK_PATH, '$LIB$', $file);
            $file = str_replace($framework->appPath, '$APP$/', $file);
        }

        $signalSearcher = new NodeVisitor_SignalSearcher;

        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($signalSearcher);

        // parse using a emulative lexer
        $factory = new \PhpParser\ParserFactory;
        $parser = $factory->create(\PhpParser\ParserFactory::PREFER_PHP7);
        $statements = $parser->parse($code);
        $traverser->traverse($statements);

        // found
        $found = $signalSearcher->found;

        foreach ($found as &$slots)
        {
            foreach ($slots as &$registered)
            {
                $registered['file'] = $file;
            }
        }

        return $found;
    }
}