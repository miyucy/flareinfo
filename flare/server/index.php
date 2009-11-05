<?php
require_once('flare/server.php');
require_once('flare/server/node/factory.php');
class Flare_Server_Index extends Flare_Server
{
    public function add_master($key, $partition=0)
    {
        if(!is_numeric($partition) || (int)$partition < 0)
        {
            throw new Exception;
        }
        $partition = (int)$partition;
        foreach($this->nodes as $node)
        {
            if($node->partition() == $partition)
            {
                throw new Exception;
            }
        }
        $node = $this->nodes($key);

        return $this->is_complete($this->send("node role {$node->host()} {$node->port()} master 1 {$partition}"));
    }

    public function active_master($key)
    {
        if(!$this->is_ready($key))
        {
            throw new Exception;
        }
        $node = $this->nodes($key);

        return $this->is_complete($this->send("node state {$node->host()} {$node->port()} active"));
    }

    public function is_ready($key)
    {
        $node = $this->nodes($key);
        if(!$node->is_master())
        {
            throw new Exception;
        }

        return $node->is_ready();
    }

    public function add_slave($key, $master_key)
    {
        $node = $this->nodes($key);
        $master = $this->nodes($master_key);
        if($node->is_master() || !$master->is_master())
        {
            throw new Exception;
        }

        return $this->is_complete($this->send("node role {$node->host()} {$node->port()} slave 0 {$master->partition()}"));
    }

    public function active_slave($key)
    {
        $node = $this->nodes($key);
        if(!$node->is_slave() || $node->is_active())
        {
            throw new Exception;
        }

        return $this->set_balance($key);
    }

    public function inactive_slave($key)
    {
        $node = $this->nodes($key);
        if(!$node->is_slave() || !$node->is_active())
        {
            throw new Exception;
        }

        return $this->set_balance($key, 0);
    }

    public function add_proxy($key)
    {
        $node = $this->nodes($key);
        if($node->is_master())
        {
            throw new Exception;
        }

        return $this->is_complete($this->send("node role {$node->host()} {$node->port()} proxy 1 0"));
    }

    public function set_balance($key, $balance=1)
    {
        $node = $this->nodes($key);
        if(!is_numeric($balance) || $balance < 0)
        {
            throw new Exception;
        }

        $command = "node role {$node->host()} {$node->port()} {$node->role()} {$balance} {$node->partition()}";
        return $this->is_complete($this->send($command));
    }

    public function servers()
    {
        return $this->create_servers();
    }

    public function nodes($node)
    {
        $this->create_servers();
        if(empty($this->nodes[$node]))
        {
            throw new Exception;
        }
        return $this->nodes[$node];
    }

    public function stats_nodes()
    {
        $prop = "role|state|partition|balance|thread_type";
        $this->nodes = array();
        $this->stats_nodes = array();
        foreach($this->send("stats nodes") as $line)
        {
            if(preg_match("/\ASTAT ([^:]+):(\d+):({$prop}) (.*)\Z/", $line, $matches))
            {
                list($dummy,$host,$port,$opt,$val) = $matches;
                $key = "{$host}:{$port}";
                $this->stats_nodes[$key][$opt] = $val;
            }
        }
        return $this->stats_nodes;
    }

    private function create_servers()
    {
        if(count($this->nodes) == 0)
        {
            foreach($this->stats_nodes() as $key => $node_info)
            {
                $this->nodes[$key] = Flare_Server_Node_Factory::factory($key, $node_info);
            }
        }
        return $this->nodes;
    }

    protected function stats_all()
    {
        parent::stats_all();
        //$this->stats_nodes();
    }

    private $nodes;
    private $stats_nodes = null;
}
