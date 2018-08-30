<?php 
namespace edrard\Atm\Database;


interface IntDbManipulate {
    public function truncateTable();
    public function deleteTable();
    public function checkTable();
    public function creatTable(array $opts);
    public function updateTable();
} 