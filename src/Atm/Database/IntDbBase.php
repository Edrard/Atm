<?php 
namespace edrard\Atm\Database;


interface IntDbBase {
    public function changeTable($table);
    public function changePrefix($prefix);
    public function setFields($fields);
    public function fullLog();
} 