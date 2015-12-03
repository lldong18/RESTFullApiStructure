<?php
namespace ApiCreator\Annotations;

/**
 * Describe a converter used by silex.
 *
 * @see http://silex.sensiolabs.org/doc/usage.html#route-variables-converters
 *
 * @Annotation
 * @Target("METHOD")
 */
class Convert
{
    /** @var string */
    public $name;

    /** @var string */
    public $method;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        if (!isset($array['convert'])) {
            $array['convert'] = array();
        }

        $array['convert'][] = array(
            'name'     => $this->name,
            'method' => $this->method,
        );
    }
}
