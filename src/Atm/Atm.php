<?php
namespace edrard\Atm;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use edrard\Log\MyLog;
use edrard\Atm\Exception\CriticalAtmException;
use edrard\Atm\Database\DbManipulate;
use edrard\Atm\ConstructData;
use edrard\Atm\Database\DataManipulate;

class Atm
{ 
    protected $separator = '|'; // Separator for columns
    protected $db = FALSE;
    protected $dm = FALSE;
    protected $fields = array();
    protected $data = array();
    protected $add = array('theme' => array(), 'opts' => array() );
    protected $batch_data = array();

    function __construct(DbManipulate $db, DataManipulate $dm, $separator = '|')
    {  
        MyLog::init('logs','atm');
        MyLog::changeType(array('warning','error','critical'),'atm');
        $this->db = $db;
        $this->dm = $dm;
        $this->changeSeparator($separator);
    }
    /**
    * Full log on
    * 
    */
    public function fullLog(){       
        MyLog::changeType(array('info','warning','error','critical'),'atm');
        MyLog::info("Full Log on",array(),'atm'); 
    }
    /**
    * Change separtor
    * 
    * @param string $separator
    */
    public function changeSeparator($separator){
        if($separator != $this->separator){
            MyLog::info("Separator change on: ".$separator,array(),'atm');
        } 
        $this->separator = $separator;
    }
    /**
    * Constructing data, checking its type. If table is creating, then adding custom properties to additional rows
    * 
    * @param array $data - main data
    * @param array $add - additional rows in forms like array('id' => array('value' => '1', 'auto_inc' => TRUE), 'time' => array('value' => '1343535', 'index' => TRUE))
    * @param array $unset - keys what we want to unset in main data
    * @return Atm
    */
    public function constructData(array $data = array(),array $add = array(),array $unset = array()){
        if(empty($data)){
            MyLog::warning("Empty Array Gived",array(),'atm');
            return FALSE;
        }
        if(!is_array($data)){
            MyLog::error("Data not an array",array(),'atm');
            return FALSE;
        } 
        $add = $this->customAddOptions($add);
        list($this->fields, $this->data) = ConstructData::construct($data,$this->separator,$add,$unset);
        return $this;
    }
    /**
    * Spliting additional rows and those custome property
    * 
    * @param array $add
    */
    private function customAddOptions(array $add){
        $value = array();
        foreach($add as $key => $val){
            $value[$key] = $val['value'];
            unset($add[$key]['value']);
        }
        if(!empty($value)){
            list($this->add['theme'], $tmp) 
            = ConstructData::construct($value,$this->separator,array(),array()); 
            $this->add['opts'] = $add;
        }    
        return $value;
    }
    /**
    * Checking Mysql Creating table if its not exist and updated with current data type.
    * 
    * @param array $fields
    * @return Atm
    */
    public function checkMysql(array $fields = array()){
        if(!empty($fields)){
            $this->fields = $fields;                
        }
        $this->db->setFields($this->fields);
        $this->db->checkTable() === TRUE ? $this->db->updateTable() : $this->db->creatTable($this->add);
        return $this;
    }
    /**
    * Insert Date to table
    * 
    */
    public function insertData(){
        $this->_insertData($this->data);
        MyLog::info("Single data to table ".$this->db->getCurrentTable(),array(),'atm');  
        return $this;
    }
    /**
    * Insert Date to table Internal Function. Separated for Logs
    * 
    */
    private function _insertData($data){
        $this->dm->insertData($data);
    }
    /**
    * Truncate current Table
    * 
    */
    public function truncateTable(){
        $this->db->truncateTable();
        MyLog::info("Truncating table ".$this->db->getCurrentTable(),array(),'atm');
        return $this;
    }
    /**
    * Drop current Table
    * 
    */
    public function dropTable(){
        $this->db->deleteTable();
        MyLog::info("Drop table ".$this->db->getCurrentTable(),array(),'atm');
        return $this;
    }
    /**
    * Change table name
    * 
    * @param string $table
    * @return Atm
    */
    public function changeTable($table){
        $this->db->changeTable($table);
        $this->dm->changeTable($table);
        MyLog::info("Changing table to ".$table,array(),'atm');
        return $this;
    }
    /**
    * Change base prefix
    * 
    * @param string $prefix
    * @return Atm
    */
    public function changePrefix($prefix){
        $this->db->changePrefix($prefix);
        $this->dm->changePrefix($prefix);
        MyLog::info("Changing prefix to ".$prefix,array(),'atm');
        return $this;
    }
    /**
    * Getdata from Database
    * 
    * @param string $order - SQL statment
    * @param mixed $custom - SQL custome request
    */
    public function getData($order = '',$custom = FALSE){
        return ConstructData::constructArray($this->dm->getData($order,$custom));
    }
    /**
    * Resset batch container
    * 
    */
    public function ressetBatch(){
        $this->batch_data = array();
        return $this;
    }
    /**
    * Insert Batch data
    * 
    * @param int $batch - ammount of collected data to insert
    * @return Atm
    */
    public function insertDataBatch($batch){
        if(!empty($this->data)){
            $this->batch_data[] = $this->data;
        }
        $this->data = array();
        $count = count($this->batch_data);
        if($count >= $batch){
            $this->_insertData($this->batch_data); 
            $this->ressetBatch();  
            MyLog::info("Inserted number of Rows - ".$count." to table ".$this->db->getCurrentTable(),array(),'atm');
        }
        return $this;    
    }
    /**
    * Finish after Batch insert
    * 
    */
    public function insertFinish(){
        return $this->insertDataBatch(1);
    }
}