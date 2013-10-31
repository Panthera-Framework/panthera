<?php
/**
  * Panthera Worker - Skeleton of pantheraCLI application for massive data processing
  *
  * @package Panthera\modules\worker
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

class pantheraWorker extends cliApp
{
    protected static $maxThreads = 4;
    protected static $maxPerThread = 10;
    
    protected $socketAddress = null; // custom socket address (can be TCP, UDP or UNIX)
    protected $db = array(); // here will be stored all items
    protected $logging = True;
    protected $quietWorkers = True;
    protected $refreshDatabseOnFinish = False;
    
    // counters
    protected $assigned = array(); // list of elements already assigned to any process
    protected $processedItems = 0;

    /**
      * Main function - initialize master or worker thread
      *
      * @author Damian Kęska
      */

    public function main()
    {
        $this -> panthera -> logging -> printOutput = $this->logging;
        $this -> panthera -> add_option('page_load_ends', array($this, 'closeThreads'));
    
        if (isset($this->argv['long']['master']))
        {
            return $this -> masterModeInit();
        }
        
        $this -> workerModeInit();
    }
    
    /**
      * Do something on exit, maybe regenerate the database?
      *
      * @return bool|array
      * @author Damian Kęska
      */
    
    public function onExit()
    {
        return False;
    }
    
    /**
      * Get database which will be used as source for data validation
      *
      * @example
      * @author Damian Kęska
      */
    
    protected function getDatabase()
    {
        return array(
            array(1, 5),
            array(134, 2187),
            array(123, 5211)
        );
    }
    
    /**
      * Parse results received from client
      *
      * @param array $results
      * @return bool
      * @author Damian Kęska
      */
    
    protected function parseResults($results)
    {
        return True;
    }
    
    /**
      * Master mode should start all working threads and manage them
      *
      * @author Damian Kęska
      */
    
    protected function masterModeInit()
    {
        // our database
        $this->db = $this->getDatabase();
        $spawnWorkers = static::$maxThreads;
        //$socketAddress = 'tcp://0.0.0.0:8000';
        
        if (!$this->socketAddress)
            $socketAddress = $this->socketAddress = 'unix:///tmp/pantheraWorker-' .rand(999, 9999). '.sock';
        
        if (count($this->db)/static::$maxPerThread < $spawnWorkers)
        {
            $spawnWorkers = intval(count($this->db)/static::$maxPerThread);
            
            if ($spawnWorkers < 1)
            {
                $spawnWorkers = 1;
            }
        }
        
        for ($i=0; $i < $spawnWorkers; $i++)
        {
            $this -> panthera -> logging -> output('Spawning worker process #' .$i, 'pantheraWorker');
            $this->spawnWorkerProcess($socketAddress);
        }
        
        $socket = @stream_socket_server($socketAddress, $errno, $errstr);
        
        if (!$socket)
        {
            $this -> panthera -> logging -> output('Cannot create socket server at ' .$socketAddress. ', errstr=' .$errstr, 'pantheraWorker');
            pa_exit();
        }
        
        while ($client = @stream_socket_accept($socket)) 
        {
            $this -> panthera -> logging -> output ('Worker connected, reading input data', 'pantheraWorker');
            $request = json_decode(fread($client, 2048), true);
            
            if ($request['type'] == 'job')
            {
                $this -> panthera -> logging -> output('Got a job request', 'pantheraWorker');
                $newArr = array();
                $i = 0;
            
                foreach ($this->db as $key => $dataRow)
                {
                    if (!$this->assigned[$key])
                    {
                        $i++;
                        $newArr[$key] = $dataRow;
                        $this->assigned[$key] = True;
                        
                        if ($i >= static::$maxPerThread)
                        {
                            break;
                        }
                    }
                }
                
               @fwrite($client, json_encode($newArr));
            } elseif ($request['type'] == 'results') {
                $this -> panthera -> logging -> output('Got results from worker, passing to parser', 'pantheraWorker');
                @fwrite($client, json_encode(array('response' => $this->parseResults($request['data']))));
                $this -> processedItems += count($request['data']);
            }
            
            if ($this -> processedItems >= count($this->db))
            {
                echo '=============================aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa======================';
            
                if ($this->refreshDatabseOnFinish)
                {
                    $this -> panthera -> logging -> output('Refreshing database', 'pantheraWorker');
                    $this -> db = $this -> getDatabase();
                } else {
                    $test = $this -> onExit();
                    
                    if ($test)
                    {
                        $this -> panthera -> logging -> output('Executing onExit action', 'pantheraWorker');
                        $this -> db = $test;
                        
                        if (!$this->db)
                        {
                            @fclose($socket);
                            pa_exit();
                        }
                        
                    } else {
                        $this -> panthera -> logging -> output('No more work to do, closing server connection', 'pantheraWorker');
                        @fclose($socket);
                        pa_exit();
                    }
                }
            }
            
            @fclose($client);
        }
        
        

        @fclose($socket);      
        //$this -> closeThreads();  
        
        
        /*while (True)
        {
            if (trim(strtolower($this->wait(1000))) == 'q') // sleep for 1000 seconds and then check every working thread
            {
                pa_exit();
            }
        }*/
    }
    
    /**
      * Spawn a worker process in background
      *
      * @author Damian Kęska
      */
    
    protected function spawnWorkerProcess($socketAddress)
    {
        $quiet = '';
    
        if ($this->quietWorkers)
        {
            $quiet = '> /dev/null 2> /dev/null';
        }
    
        $process = proc_open($_SERVER['_']. ' --socket=' .$socketAddress. ' ' .$quiet. ' &', array(), $pipes);
        $this -> workers[] = proc_get_status($process);
        proc_close($process);
    }
    
    /**
      * Close all threads on application exit
      *
      * @author Damian Kęska
      */
    
    public function closeThreads()
    {
        $this->panthera->logging->output('Calling all threads to finish all jobs', 'pantheraWorker');
    }
    
    /**
      * Get job for working thread
      *
      * @return array
      * @author Damian Kęska
      */
    
    protected function workerGetJob()
    {
        $d = array(
            'type' => 'job',
            'worker_id' => posix_getpid()
        );
        
        return $this->_clientSendData($d);
    }
    
    /**
      * Processing data
      *
      * @param array $db
      * @return array
      * @author Damian Kęska
      */
    
    protected function workerProcessData($db)
    {
        $results = array();
    
        foreach ($db as $key => $value)
        {
            $results[$key] = $value[0]*$value[1];
        }
        
        return $results;
    }
    
    /**
      * Send processed results back to master process
      *
      * @param array $data
      * @package Panthera\Package
      * @author Damian Kęska
      */
    
    protected function workerSendResults($data)
    {
        $d = array(
            'type' => 'results',
            'worker_id' => posix_getpid(), 
            'data' => $data
        );
        
        return $this->_clientSendData($d);
    }
    
    /**
      * Send data to server
      *
      * @param array $data
      * @return array
      * @author Damian Kęska
      */
    
    protected function _clientSendData($data)
    {
        $socket = stream_socket_client($this->argv['long']['socket'], $errorno, $errorstr, 32);
        
        if (!$socket)
        {
            $this -> panthera -> logging -> output('Master is down, closing thread', 'pantheraWorker');
            pa_exit();
        }
        
        fwrite($socket, json_encode($data));
        $response = '';
        
        while (!@feof($socket))
        {
            $response .= fread($socket, 1024);
        }
        
        return json_decode($response, true);
    }
    
    /**
      * Worker thread initialization
      *
      * @author Damian Kęska
      */
    
    protected function workerModeInit()
    {
        $data = $this -> workerGetJob();
        
        if (!$data)
        {
            $this -> panthera -> logging -> output('Worker finished all jobs.', 'pantheraWorker');
            return False;
        }
        
        $this -> workerSendResults ($this -> workerProcessData($data));
        $this -> workerModeInit(); // get new data again
    }
}
