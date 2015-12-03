<?php
namespace Wsbox\Validator\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validator exception may be thrown after failed collection validation.
 */
class CollectionValidatorException extends \RuntimeException
{
    protected $violations;

    /**
     * Constructor
     *
     * The default value of 400 for code indicates a bad request if this exception is used in a web app
     *
     * @param ConstraintViolationList $violations violation list from Symfony validator
     * @param integer                 $code       exception code
     * @param Exception               $previous   previous exception
     *
     * @throws \LogicException When no constraints passed in
     */
    public function __construct(ConstraintViolationList $violations, $code = 400, Exception $previous = null)
    {
        if (count($violations) === 0) {
            $message = 'CollectionValidatorException should be called with at least one constraint violation';
            throw new \LogicException($message);
        }

        $this->violations = $violations;
        $message = implode('|', $this->getMessages());
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the list of messages in an array
     *
     * @return array Error messages
     */
    public function getMessages()
    {
        $messages = array();
        foreach ($this->violations as $this->violation) {
            $messages[] = $this->violation->getMessage();
        }

        return $messages;
    }
}
