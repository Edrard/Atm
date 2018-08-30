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

    public function __construct(QueryBuilderHandler $db,$table,$prefix = ''){
        MyLog::init();
        $this->db = $db;
        $this->changeTable($table);
        $this->changePrefix($prefix);
    } 
    public function changePrefix($prefix){
        if($prefix != $this->prefix){
            MyLog::info("[".get_class($this)."] Prefix changed on: ".$prefix);
        } 
        $this->prefix = $prefix;
    }
    public function setFields($fields){
        if($fields != $this->fields){
            MyLog::info("Setted Fields");
        } 
        $this->fields = $fields;
    }
    public function changeTable($table){
        try{
            if(!$table && !is_string($table)){
                throw new CriticalAtmException("[".get_class($this)."] Table name not setted");
            }
            if($table != $this->table){
                MyLog::info("[".get_class($this)."] Table changed on: ".$table);
            } 
            $this->table = $table;    
        }Catch(\CriticalAtmException $e){
            die($e->getMessage());
        }
    }
}
