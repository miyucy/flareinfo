<?php /* coding:utf-8 */
require_once('flare/server/node.php');
require_once('flare/server/node/proxy.php');
require_once('flare/server/node/master.php');
require_once('flare/server/node/slave.php');
class Flare_Server_Node_Factory
{
    static function factory($key, $info)
    {
        $role = $info['role'];
        if(strcmp($role, "master") == 0)
        {
            return new Flare_Server_Node_Master($key, $info);
        }
        if(strcmp($role, "slave") == 0)
        {
            return new Flare_Server_Node_Slave($key, $info);
        }
        if(strcmp($role, "proxy") == 0)
        {
            return new Flare_Server_Node_Proxy($key, $info);
        }
        throw new Exception;
    }
}
