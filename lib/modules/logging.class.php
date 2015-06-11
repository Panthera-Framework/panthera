<?php
namespace Panthera;

/**
 * This class is handling all messages, saving them to file, displaying
 * its used to debug whole application based on Panthera Framework
 *
 * @Package Panthera
 * @author Damian Kęska
 */
class logging
{
    public $debug = False;
    public $tofile = True;
    public $toVarCache=True;
    public $printOutput = False;
    public $filterMode = '';
    public $filter = array();
    private $_output = array();
    private $panthera;
    protected $timer = 0;
    public $isRealMemUsage = False;
    public $strict = False;

    /**
     * Constructor
     * Its just adding an event to hook session_save to allow saving data on application exit
     *
     * @param pantheraCore $panthera
     * @author Damian Kęska
     * @return \Panthera\logging
     */
    public function __construct($panthera)
    {
        $this->panthera = $panthera;
        $this->panthera -> add_option('session_save', array($this, 'saveLog'));

        if (defined('PANTHERA_FORCE_DEBUGGING'))
            $this->debug = True;
    }

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
        if(!$this->debug and !$this->printOutput)
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

        // plugins support eg. firebug
        $this->panthera->get_options('logging.output', $msg);

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
        $this -> panthera -> importModule('filesystem');

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

            $msg .= "[".substr($time, 0, 9).", ".substr($executionTime, 0, 9)."ms".$real."] [".filesystem::bytesToSize($line[4])."] [".$line[1]."] ".$line[0]. "\n";
            $lastTime = $time;
        }

        $msg .= "[".substr(microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'], 0, 9).", ".substr((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']-$lastTime)*1000, 0, 9)."ms] [".filesystem::bytesToSize(memory_get_usage($this->isRealMemUsage))."]  [pantheraLogging] Done\n";

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

        if ($this->toVarCache and $this->panthera->varCache)
            $this->panthera->varCache->set('debug.log', base64_encode($output), 864000);
    }

    /**
     * Read log from cache or from file
     *
     * @author Damian Kęska
     * @return string|bool
     */

    public function readSavedLog()
    {
        if ($this->toVarCache and $this->panthera->varCache and $this->panthera->varCache->exists('debug.log'))
            return base64_decode($this->panthera->varCache->get('debug.log'));

        if ($this->tofile and is_file(SITE_DIR. '/content/tmp/debug.log'))
            return @file_get_contents(SITE_DIR. '/content/tmp/debug.log');

        return False;
    }
}