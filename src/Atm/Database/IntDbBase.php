<?php 
namespace edrard\Atm\Database;


interface IntDbBase {
    public function changeTable($table);
    public function changePrefix($prefix);
} 