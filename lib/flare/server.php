<?php /* coding:utf-8 */
require_once('flare.php');
class Flare_Server extends Flare
{
    public function __construct($host, $port=null)
    {
        list($this->host, $this->port) = $this->split_hostaddr($host, $port);
        $this->stats_all();
    }

    public function __call($name, $args)
    {
        if(array_key_exists($name, $this->stats))
        {
            return $this->stats[$name];
        }
        if(array_key_exists($name, $this->stats_threads))
        {
            return $this->stats_threads[$name];
        }
        parent::__call($name, $args);
    }

    protected function send($command)
    {
        $buff = '';
        try
        {
            $conn = $this->open();
            fwrite($conn, $command . "\r\nquit\r\n");

            while(!feof($conn))
            {
                $buff .= fread($conn, 8 * 1024);
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
        try
        {
            $this->close();
        }
        catch(Exception $e)
        {
        }
        return $this->split_lines($buff);
    }

    protected function split_lines($str)
    {
        return explode("\r\n", $str);
    }

    protected function is_complete($array)
    {
        foreach($array as $element)
        {
            if(strcmp($element, "OK") == 0)
            {
                return true;
            }
        }
        return false;
    }

    protected function stats_all()
    {
        $this->stats();
        $this->stats_threads();
    }

    protected function stats()
    {
        $prop = "bytes_read|bytes_written|bytes|cmd_get|cmd_set|connection_structures|curr_connections|curr_items|evictions|get_hits|get_misses|limit_maxbytes|pid|pointer_size|pool_threads|rusage_system|rusage_user|threads|time|total_connections|total_items|uptime|version";
        $this->stats = array();
        foreach($this->send("stats") as $line)
        {
            if(preg_match("/\ASTAT ({$prop})\s*(.*)?\Z/", $line, $matches))
            {
                list($dummy, $opt, $val) = $matches;
                $this->stats[$opt] = $val;
            }
        }
        return $this->stats;
    }

    protected function stats_threads()
    {
        $prop = "type|peer|op|uptime|state|info|queue";
        $this->stats_threads = array();
        foreach($this->send("stats threads") as $line)
        {
            if(preg_match("/\ASTAT (\d+):({$prop})\s*(.*)?\Z/", $line, $matches))
            {
                list($dummy,$thread_id,$opt,$val) = $matches;
                $this->stats_threads[$thread_id][$opt] = $val;
            }
        }
        return $this->stats_threads;
    }

    private function open()
    {
        if(is_resource($this->conn))
        {
            throw new Exception;
        }
        $this->conn = @fsockopen($this->host, $this->port);
        if(!is_resource($this->conn))
        {
            throw new Exception;
        }
        return $this->conn;
    }

    private function close()
    {
        if(is_resource($this->conn))
        {
            fclose($this->conn);
        }
        $this->conn = null;
    }

    private function split_hostaddr($host, $port=null)
    {
        if(is_null($port))
        {
            list($host, $port) = explode(':', $host);
        }
        if(strlen($host) == 0 || !is_numeric($port))
        {
            throw new Exception;
        }
        return array($host, $port);
    }

    protected $host, $port;
    private   $conn = null;
    protected $stats;
    protected $stats_threads;
}
