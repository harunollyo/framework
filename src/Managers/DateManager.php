<?php

namespace Framework\Managers;

use BadMethodCallException;
use DateTime;

class DateManager
{
    /**
     * Handle the Carbon date functions. Forward the date methods from Date Facade to Carbon.
     * 
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        $date = new DateTime();

        if (!method_exists($date, $method)) {
            throw new BadMethodCallException("Call to undefined method Framework\Managers\DateManager::$method");
        }

        return $date->$method(...$parameters);
    }
}
