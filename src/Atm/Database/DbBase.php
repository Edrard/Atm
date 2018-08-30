<?php
namespace edrard\Atm\Database;

use edrard\Log\MyLog;
use Pixie\QueryBuilder\QueryBuilderHandler;
use edrard\Atm\Exception\CriticalAtmException;
use edrard\Atm\Database\IntDbBase;

class DbBase implements IntDbBase
{
    protected $db = FALSE;
    protected $table = '';
    protected $fields = array();
    protected $prefix = '';
    protected $mtype = 'MyISAM'; 
    protected $class;

    public function __construct(QueryBuilderHandler $db,$table,$prefix = ''){
        $this->class = string_split_last(get_class($this));
        MyLog::init('logs',$this->class);
        $this->db = $db;
        $this->changeTable($table);
        $this->changePrefix($prefix);
    } 
    public function changePrefix($prefix){
        if($prefix != $this->prefix){
            MyLog::info("Prefix changed on: ".$prefix,array(),$this->class);
        } 
        $this->prefix = $prefix;
    }
    public function setFields($fields){
        if($fields != $this->fields){
            MyLog::info("Setted Fields",array(),$this->class);
        } 
        $this->fields = $fields;
    }
    public function changeTable($table){
        try{
            if(!$table && !is_string($table)){
                throw new CriticalAtmException("Table name not setted",array(),$this->class);
            }
            if($table != $this->table){
                MyLog::info("Table changed on: ".$table,array(),$this->class);
            } 
            $this->table = $table;    
        }Catch(\CriticalAtmException $e){
            die($e->getMessage());
        }
    }
}
