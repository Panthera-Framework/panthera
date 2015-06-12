<?php
namespace Panthera;

/**
 * This class is handling all messages, saving them to file, displaying
 * its used to debug whole application based on Panthera Framework
 *
 * @Package Panthera
 * @author Damian Kęska
 */
class logging extends baseClass
{
    /**
     * Print output directly to console/browser
     *
     * @var bool
     */
    public $printOutput = False;

    /**
     * Messages filtering
     *
     * @var string {blacklist|whitelist}
     */
    public $filterMode = '';

    /**
     * List of message types
     *
     * @var String[]
     */
    public $filter = array();

    /**
     * @var array
     */
    private $_output = array();

    /**
     * Timer that counts time between two messages
     *
     * @var int
     */
    protected $timer = 0;

    /**
     * Show real memory usage?
     *
     * @var bool
     */
    public $isRealMemUsage = False;


    /**
     * Add a line to messages log
     *
     * @param string $msg Message
     * @param string $type Identifier for group of messages
     * @param bool $dontResetTimer Don't reset timer to keep real execution time
     * @hook logging.output
     * @return bool
     * @author Damian Kęska
     */
    public function output($msg, $type='', $dontResetTimer=False)
    {
        if(!$this->app->isDebugging and !$this->printOutput)
            return False;

        // filter
        if ($this->filterMode == 'blacklist')
        {
            if (isset($this->filter[$type]))
                return False;
        } else if ($this->filterMode == 'whitelist') {
            if (!isset($this->filter[$type]))
                return False;
        }

        $time = microtime(true);

        if ($this->printOutput)
            print($msg. "\n");

        $this->_output[] = array($msg, $type, $time, $this->timer, memory_get_usage($this->isRealMemUsage));

        if (!$dontResetTimer)
            $this->timer = 0;

        return True;
    }

    /**
     * Clear output string
     *
     * @author Damian Kęska
     * @return void
     */
    public function clear()
    {
        $this->_output = array();
    }

    /**
     * Start timer to count execution time of fragment of code
     *
     * @author Damian Kęska
     * @return void
     */
    public function startTimer()
    {
        $this->timer = microtime(true);
    }

    /**
     * Get complete output
     *
     * @author Damian Kęska
     * @return string
     */
    public function getOutput($array=False)
    {
        if ($array === True)
            return $this->_output;

        $this->output('Generating output', 'pantheraLogging');

        if (PANTHERA_MODE == 'CLI')
        {
            $defaults = "Client addr(".@$_SERVER['SSH_CLIENT'].") => CLI ".@$_SERVER['SCRIPT_NAME']."\n";
        } else
            $defaults = "Client addr(".@$_SERVER['REMOTE_ADDR'].") => ".@$_SERVER['REQUEST_METHOD']. " ".@$_SERVER['REQUEST_URI']."\n";

        $msg = '';
        $lastTime = 0;

        // convert output to string
        foreach ($this->_output as $line)
        {
            $time = $line[2]-$_SERVER['REQUEST_TIME_FLOAT'];
            $real = '';

            if ($line[3] > 0)
            {
                $executionTime = ($line[2]-$line[3])*1000;
                $real = ' real';
            } else {
                $executionTime = ($time-$lastTime)*1000;
            }

            $msg .= "[".substr($time, 0, 9).", ".substr($executionTime, 0, 9)."ms".$real."] [".fileutils::bytesToSize($line[4])."] [".$line[1]."] ".$line[0]. "\n";
            $lastTime = $time;
        }

        $msg .= "[".substr(microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'], 0, 9).", ".substr((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']-$lastTime)*1000, 0, 9)."ms] [".fileutils::bytesToSize(memory_get_usage($this->isRealMemUsage))."]  [pantheraLogging] Done\n";

        return $defaults.$msg;
    }

    /**
     * Save debug to file
     *
     * @author Damian Kęska
     * @return void
     */
    public function saveLog()
    {
        $output = $this->getOutput();

        if ($this->tofile)
        {
            $fp = @fopen(SITE_DIR. '/content/tmp/debug.log', 'w');
            @fwrite($fp, $output);
            @fclose($fp);
        }

        $this->app->cache->set('debug.log', base64_encode($output), 864000);
    }
}