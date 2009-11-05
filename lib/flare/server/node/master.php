<?php /* coding:utf-8 */
require_once('flare/server/node/storage.php');
class Flare_Server_Node_Master extends Flare_Server_Node_Storage
{
    public function is_ready()
    {
        if($this->is_prepare())
        {
            return false;
        }
        foreach($this->stats_threads() as $info)
        {
            if(strcmp($info['op'], 'dump') == 0)
            {
                return false;
            }
        }
        return true;
    }
}
