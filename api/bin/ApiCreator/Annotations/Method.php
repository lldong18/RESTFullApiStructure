<?php
namespace ApiCreator\Annotations;

/**
 * The HTTP method name an action specifies.
 *
 * ie. PUT, GET
 *
 * @Annotation
 * @Target("METHOD")
 */
class Method
{
    /** @var string */
    public $method;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['method'] = $this->method;
    }
}
