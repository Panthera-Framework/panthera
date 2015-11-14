<?php
namespace Panthera\Components\Indexing\PhpParser;

use Panthera\Classes\Utils\ClassUtils;

/**
 * A visitor class that is used as a callback to PHP-Parser
 *
 * @package Panthera\deployment\build\framework\signals
 * @author Damian Kęska <damian.keska@fingo.pl>
 */
class NodeVisitor_SignalSearcher extends \PhpParser\NodeVisitorAbstract
{
    public $found = [];
    protected $namespace = '\\';

    /**
     * Iterate through nodes and collect data
     *
     * @param \PhpParser\Node $node
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return string
     */
    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_)
        {
            $this->namespace = '\\' .implode('\\', $node->name->parts). '\\';
        }
        elseif ($node instanceof \PhpParser\Node\Stmt\Class_)
        {
            foreach ($node->stmts as $stmt)
            {
                if (!$stmt instanceof \PhpParser\Node\Stmt\ClassMethod)
                {
                    continue;
                }

                $phpDoc = $stmt->getAttribute('comments');

                if ($phpDoc)
                {
                    $phpDoc = $phpDoc[0]->getText();
                }

                $signals = ClassUtils::getTag($phpDoc, 'signal');
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