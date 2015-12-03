<?php
namespace Wsbox\Assert\Exception;

use Exception;

class AssertException extends Exception
{
    protected $devMessage = 'I don\'t know what happened ¯\_(ツ)_/¯';

    public function getDevMessage()
    {
        return $this->devMessage;
    }

    public function setDevMessage($message)
    {
        $this->devMessage = $message;
    }
}
