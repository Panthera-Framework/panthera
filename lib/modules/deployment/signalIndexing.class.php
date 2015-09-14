<?php
/**
 * Allows indexing all places where signals are attached
 *
 * @package Panthera\deployment\build\framework\signals
 * @author Damian Kęska <damian.keska@fingo.pl>
 */
class signalIndexing
{
    /**
     * Find all class methods that are registering or executing signals
     *
     * @todo Add validation, exceptions
     *
     * @param string $path Path to file
     *
     * @author Damian Kęska <damian.keska@fingo.pl>
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
     * @author Damian Kęska <damian.keska@fingo.pl>
     * @return array
     */
    public static function loadString($code, $file = '')
    {
        $framework = \Panthera\framework::getInstance();

        if ($file && is_file($file))
        {
            $file = realpath($file);
            $file = str_replace(PANTHERA_FRAMEWORK_PATH, '$LIB$', $file);
            $file = str_replace($framework->appPath, '$APP$/', $file);
        }

        $signalSearcher = new \NodeVisitor_signalSearcher;

        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor($signalSearcher);

        // parse using a emulative lexer
        $parser = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);
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

/**
 * A visitor class that is used as a callback to PHP-Parser
 *
 * @package Panthera\deployment\build\framework\signals
 * @author Damian Kęska <damian.keska@fingo.pl>
 */
class NodeVisitor_signalSearcher extends PhpParser\NodeVisitorAbstract
{
    public $found = array();
    protected $namespace = '\\';

    /**
     * Iterate through nodes and collect data
     *
     * @param PhpParser\Node $node
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function enterNode(PhpParser\Node $node)
    {
        if ($node instanceof PhpParser\Node\Stmt\Namespace_)
        {
            $this->namespace = '\\' .implode('\\', $node->name->parts). '\\';
        }
        elseif ($node instanceof PhpParser\Node\Stmt\Class_)
        {
            foreach ($node->stmts as $stmt)
            {
                if (!$stmt instanceof PhpParser\Node\Stmt\ClassMethod)
                {
                    continue;
                }

                $phpDoc = $stmt->getAttribute('comments');

                if ($phpDoc)
                {
                    $phpDoc = $phpDoc[0]->getText();
                }

                $signals = \Panthera\utils\classUtils::getTag($phpDoc, 'signal');
                //$slots = \Panthera\utils\classUtils::getTag($phpDoc, 'slot');

                if ($signals)
                {
                    foreach ($signals as $signal)
                    {
                        if (!isset($this->found[$signal]))
                        {
                            $this->found[$signal] = [];
                        }

                        $this->found[$signal][] = [
                            'type'     => 'signal',
                            'call'     => $this->namespace . $node->name. '::' .$stmt->name,
                            'phpDoc'   => $phpDoc,
                            'file'     => '',
                        ];
                    }
                }

                /*if ($slots)
                {
                    foreach ($slots as $slot)
                    {
                        if (!isset($this->found[$slot]))
                        {
                            $this->found[$slot] = [];
                        }

                        $this->found[$slot][] = [
                            'type'     => 'slot',
                            'call'     => $this->namespace . $node->name. '::' .$stmt->name,
                            'phpDoc'   => $phpDoc,
                            'file'     => '',
                        ];
                    }
                }*/
            }
        }
    }
}