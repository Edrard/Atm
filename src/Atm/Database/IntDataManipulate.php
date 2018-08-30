<?php 
namespace edrard\Atm\Database;


interface IntDataManipulate {
    public function insertData(array $data);
    public function getData($order,$custom);
} 