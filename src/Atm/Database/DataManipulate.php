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
    public function generateData(array $data){
        if(!empty($data)){
            $fields =  array();
            $insert = array();
            foreach($data as $key => $vals){
                foreach(array_keys($vals) as $field){
                    $fields[$field] = $field;
                }
            }
            foreach($data as $key => $vals){
                foreach($fields as $field){
                    $insert[$key][] = isset($vals[$field]) ? $vals[$field] : '';
                }
            }
            foreach($insert as $key => $val){
                $insert[$key] = "'".implode("','", $val)."'";    
            }
            $sql = "INSERT INTO `".$this->prefix.$this->table."` (`" . implode("`,`", $fields ) . "`) VALUES (".
            implode('),(', $insert).");";
            return $sql;
        }   
    }
}
