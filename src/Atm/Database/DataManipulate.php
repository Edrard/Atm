<?php
namespace edrard\Atm\Database;

use edrard\Log\MyLog;
use Pixie\QueryBuilder\QueryBuilderHandler;
use edrard\Atm\Exception\CriticalAtmException;
use edrard\Atm\Database\DbBase;
use edrard\Atm\Database\IntDataManipulate;

class DataManipulate extends DbBase implements IntDataManipulate
{
    public function insertData(array $data){
        if(!empty($data)){
            $this->db->table($this->prefix.$this->table)->insert($data);
        }   
    }
    public function getData($order = '',$custom = FALSE){  // $order = 'ORDER BY `|type` ASC'
        if($custom == FALSE){
            return $this->db->query('SELECT * FROM `'.$this->prefix.$this->table.'` '.$order.';')->setFetchMode(\PDO::FETCH_ASSOC)->get();    
        }else{
            return $this->db->query($custom)->setFetchMode(\PDO::FETCH_ASSOC)->get(); 
        }
    }

}
