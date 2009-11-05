<?php /* coding:utf-8 */
require_once('flare/server.php');
class Flare_Server_Node extends Flare_Server
{
    public function __construct($key, $info)
    {
        parent::__construct($key);
        $this->key = $key;
        $this->info = $info;
    }

    public function key()
    {
        return $this->key;
    }

    public function host()
    {
        return $this->host;
    }

    public function port()
    {
        return $this->port;
    }

    public function partition()
    {
        if(strlen($this->info["partition"]) == 0 ||
           !is_numeric($this->info["partition"]))
        {
            throw new Exception;
        }
        return $this->info["partition"] = (int)$this->info["partition"];
    }

    public function role()
    {
        if(strlen($this->info["role"]) == 0)
        {
            throw new Exception;
        }
        return $this->info["role"];
    }

    public function is_master()
    {
        return strcmp($this->role(), "master") == 0;
    }

    public function is_slave()
    {
        return strcmp($this->role(), "slave") == 0;
    }

    public function is_proxy()
    {
        return strcmp($this->role(), "proxy") == 0;
    }

    public function state()
    {
        if(strlen($this->info["state"]) == 0)
        {
            throw new Exception;
        }
        return $this->info["state"];
    }

    public function is_active()
    {
        return strcmp($this->state(), "active") == 0;
    }

    public function is_prepare()
    {
        return strcmp($this->state(), "prepare") == 0;
    }

    public function is_down()
    {
        return strcmp($this->state(), "down") == 0;
    }

    protected $key;
    protected $info;
}
