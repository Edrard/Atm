<?php
namespace edrard\Atm;

use edrard\Atm\Exception\CriticalAtmException;
use edrard\Packadges\ArrayStrConv;
use edrard\Atm\ConstructData;

class ConstructData
{
    protected static $level = array('INT' => 10,'BIGINT' => 19,'VARCHAR' => 50,'TEXT' => 255);
    protected static $unset = array();
    protected static $add = array();
    protected static $separator = '|'; // Separator for columns

    public static function changeSeparator($separator = '|'){
        static::$separator = $separator;    
    }
    public static function changeUnset(array $unset = array()){
        static::$unset = $unset;    
    }
    public static function changeLevels($level = array()){
        static::$level = $level;
    }
    public static function compareTypeMysql($name,$comp_1,$comp_2,$unset = FALSE){
        if(static::$level[$comp_1['type']] > static::$level[$comp_2['type']]){

            $tmp['type'] = $comp_1['type'];
            $tmp['size'] = static::$level[$comp_1['type']];
            if($unset === TRUE){
                return array();
            }

        } else if(static::$level[$comp_1['type']] < static::$level[$comp_2['type']]){

            $tmp['type'] = $comp_2['type'];
            $tmp['size'] = static::$level[$comp_2['type']];

        } else if(static::$level[$comp_1['type']] == static::$level[$comp_2['type']]){
            $tmp['type'] = $comp_2['type'];
            $tmp['size'] = max($comp_1['size'],$comp_2['size']);
            if($unset === TRUE){
                if($comp_1['size'] >= $comp_2['size']){
                    return array();
                }
            }
        }
        if($tmp['type'] == 'TEXT'){
            $tmp['size'] = ''; 
        }
        return $tmp;
    }
    private static function detectTypeMysql($string){ 
        if(is_float($string)){
            return array('type' => 'VARCHAR','size' => static::$level['VARCHAR']);    
        }
        if(is_numeric($string)){
            $string = abs($string);
            if(strlen($string) < 8){
                return array('type' => 'INT','size' => static::$level['INT']);
            }
            return array('type' => 'BIGINT','size' => static::$level['BIGINT']);
        }    
        if(mb_strlen($string) < static::$level['TEXT']){
            return array('type' => 'VARCHAR', 'size' => mb_strlen($string));
        }
        return array('type' => 'TEXT', 'size' => '');
    }
    public static function construct(array $data = array(),$separator = FALSE,array $add = array(), array $unset = array()){
        static::$separator = $separator !== FALSE ? $separator : static::$separator;
        static::$unset = $unset;
        static::$add = $add;
        return static::preConstruct($data);

    }
    private static function preConstruct(array $data){
        $fields = array();
        $fields = array_special_merge_samere(static::$add,ArrayStrConv::construct_string($data,'',static::$separator,static::$unset));
        return [static::arrayMove($fields),$fields];
    }
    private static function arrayMove(array $fields){  
        return array_map('self::detectTypeMysql',$fields);                                                  
    }
    public static function constructArray($array){
        foreach($array as $key => $val){
            $array[$key] = ArrayStrConv::construct_array($val);  
        } 
        dd($array);     
    }
}
