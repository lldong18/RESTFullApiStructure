<?php
namespace ApiCreator\Annotations;

/**
 * Annotation to describe a before method for a controller or action.
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Before
{
    /** @var string */
    public $method;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['before'] = $this->method;
    }
}
