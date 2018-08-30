<?php
namespace edrard\Atm\Database;

use edrard\Log\MyLog;
use Pixie\QueryBuilder\QueryBuilderHandler;
use edrard\Atm\Database\IntDbManipulate;
use edrard\Atm\Exception\CriticalAtmException;
use edrard\Atm\Database\DbBase;
use edrard\Atm\ConstructData;

class DbManipulate extends DbBase implements IntDbManipulate
{

    public function getCurrentTable(){
        return $this->prefix.$this->table;    
    }
    public function checkTable(){
        $test = $this->db->query('SHOW TABLES LIKE "'.$this->prefix.$this->table.'";')->get();
        return !empty($test) ? TRUE : FALSE;
    }
    private function listColumns(){  
        $list = $this->db->query("SHOW COLUMNS FROM `".$this->prefix.$this->table."` ;")->get();
        foreach($list as $val){ 
            preg_match('/(?<=\()(.+)(?=\))/is', $val->Type, $match);
            $new[$val->Field]['size'] = isset($match[0]) ? $match[0] : '';
            $new[$val->Field]['type'] =  strtoupper(preg_replace('/\(.*\)/', '', $val->Type));
        } 
        return $new;
    }
    private function checkColumn($field){
        $sql = "SHOW COLUMNS FROM `".$this->prefix.$this->table."` WHERE Field = '".$field."'";
        $list = $this->db->query($sql)->get();
        if(!empty($list)){
            return TRUE;
        }    
        return FALSE;
    }
    public function creatTable(array $addopts){
        $in = array();
        foreach($this->fields as $key => $val){
            if($val['type'] == 'TEXT'){
                $in[] = '`'.$key.'` TEXT NOT NULL';
            }else{
                $in[] = '`'.$key.'` '.$val['type'].'('.$val['size'].') NOT NULL';
            }    
        }

        $sql = "CREATE TABLE IF NOT EXISTS `".$this->prefix.$this->table."` (
        ".(implode(',',$in))."
        ) ENGINE=".$this->mtype." DEFAULT CHARSET=utf8;"; 
        MyLog::info("[".get_class($this).'] Creating table: '.$this->prefix.$this->table.' with columns: '.(implode(',',$in)));
        $this->db->query($sql);
        foreach($addopts['theme'] as $key => $val){
           $this->changeRowCustom($key,$val,$addopts['opts'][$key]);       
        }
    }
    private function generateCustom($rowname,$opts){
        $custome = '';
        if(isset($opts['auto_inc']) && $opts['auto_inc'] !== FALSE){
            $custom = 'AUTO_INCREMENT , ADD PRIMARY KEY (  `'.$rowname.'` )';   
        }elseif(isset($opts['primary']) && $opts['primary'] !== FALSE){
            $custom = ' , ADD PRIMARY KEY (  `'.$rowname.'` )';
        }elseif(isset($opts['index']) && $opts['index'] !== FALSE){
            $custom = ' , ADD INDEX (  `'.$rowname.'` )';
        }
        return $custom;   
    }
    public function createRowCustom($rowname,$rowopt,$opts ){
        $this->createRow($rowname,$rowopt,$this->generateCustom($rowname,$opts));
    }
    public function changeRowCustom($rowname,$rowopt,$opts ){
        $this->changeRow($rowname,$rowopt,$this->generateCustom($rowname,$opts));
    }
    private function createRow($rowname,$rowopt, $custome = '') {    // rowopt - array(type,size)  type - MySQL type, size if needed
        if(!$this->checkColumn($rowname)){
            if($rowopt['size']){
                $rowopt['size'] = ' ('.$rowopt['size'].') ';
            }
            $sql = 'ALTER TABLE `'.$this->prefix.$this->table.'`  ADD `'.$rowname.'` '.$rowopt['type'].$rowopt['size'].' NOT NULL '.$custome;
            $this->db->query($sql);
            MyLog::info("[".get_class($this).'] New row in table: '.$this->prefix.$this->table. ' name - '.$rowname.' value - '.json_encode($rowopt));
        }
    }
    private function changeRow($rowname,$rowopt,$custom = ''){     // rowopt - array(type,size)  type - MySQL type, size if needed
        if($this->checkColumn($rowname)){
            if($rowopt['size']){
                $rowopt['size'] = ' ('.$rowopt['size'].') ';
            }
            $sql = 'ALTER TABLE `'.$this->prefix.$this->table.'` CHANGE `'.$rowname.'` `'.$rowname.'` '.$rowopt['type'].$rowopt['size'].' NOT NULL '.$custom;
            $this->db->query($sql);
            MyLog::info("[".get_class($this).'] Update row in table: '.$this->prefix.$this->table. ' name - '.$rowname.' value - '.json_encode($rowopt));
        }
    }
    public function updateTable(){
        $current = $this->listColumns();
        $upd = array();
        $new = array();
        foreach($this->fields as $key => $lav){
            if(isset($current[$key])){
                $upd[$key] = ConstructData::compareTypeMysql($key,$current[$key],$lav,TRUE);
                if(empty($upd[$key])){
                    unset($upd[$key]);
                }
            }else{
                $new[$key] = $lav; 
            }
        } 
        if(!empty($new)){
            foreach($new as $key => $val){
                $this->createRow($key,$val);
            }
        } 
        if(!empty($upd)){
            foreach($upd as $key => $val){
                $this->changeRow($key,$val);
            }
        } 
        if(empty($new) && empty($upd)){
            MyLog::info("[".get_class($this).'] No changes in table: '.$this->prefix.$this->table);    
        }
    }
    public function truncateTable(){
        if($this->checkTableNameExist() !== FALSE){
            $sql = 'TRUNCATE TABLE '.$this->prefix.$this->table;
            $this->db->query($sql);
            MyLog::info("[".get_class($this).'] Truncate table: '.$this->prefix.$this->table);
        }
    }
    public function deleteTable(){
        if($this->checkTableNameExist() !== FALSE){
            $sql = 'DROP TABLE `'.$this->prefix.$this->table.'`;';
            $this->db->query($sql);
            MyLog::info("[".get_class($this).'] Dropped table: '.$this->prefix.$this->table);
        }
    }
    public function checkTableNameExist(){
        $sql = "SHOW TABLES LIKE '".$this->prefix.$this->table."';";
        $list = $this->db->query($sql)->get();
        if(!empty($list)){
            return TRUE;
        }    
        return FALSE;
    }
}
