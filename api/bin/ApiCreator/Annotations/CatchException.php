<?php
namespace ApiCreator\Annotations;

/**
 * Describe an exception that is caught by the API.
 * It can be on a controller or action level.
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class CatchException
{
    /**
     * The name of the exception to catch.
     * @var string
     */
    public $name;

    /**
     * The exception code.
     * @var int
     */
    public $code;

    /**
     * Create a response with this status code.
     * @var int
     */
    public $responseCode;

    /**
     * Create a response with this set as the message.
     * @var string
     */
    public $responseMessage;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        // no exception to catch
        if (!$this->name) {
            return;
        }

        if (!isset($array['exceptions'])) {
            $array['exceptions'] = array();
        }

        $name = $this->name;
        if (!isset($array['exceptions'][$name])) {
            $array['exceptions'][$name] = array();
        }

        $array['exceptions'][$name][] = array(
            // if no code specified, use * as default case
            'code' => $this->getCode(),
            'responseCode' => $this->getResponseCode(),
            'responseMessage' => $this->getResponseMessage(),
        );
    }

    /**
     * Get the exception code, if none specified, return '*' wildcard.
     * The '*' is used to determine if we need to make a 'default' block in a
     * switch statement.
     *
     * @return int|string
     */
    public function getCode()
    {
        return $this->code ?: '*';
    }

    /**
     * The HTTP response code for this exception.
     *
     * @return int|false
     */
    public function getResponseCode()
    {
        return $this->responseCode ?: false;
    }

    /**
     * A user friendly message for this exception.
     *
     * @return string|false
     */
    public function getResponseMessage()
    {
        return $this->responseMessage ?: false;
    }
}
