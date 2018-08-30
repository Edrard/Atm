<?php
namespace edrard\Atm\Exception;

use edrard\Log\MyLog;

class CriticalAtmException extends \Exception
{
    public function __construct($message)
    {
        $args = func_get_args();
        $message = $this->create($args);
        $code = isset($args[2]) ? (int) $args[2] : 0;
        parent::__construct($message,$code);
    }

    protected function create(array $args)
    {
        if (MyLog::status() !== FALSE){ 
            MyLog::critical($args[0]."\n".$this->getTraceAsString());    
        }
        return $args[0];
    }
}
