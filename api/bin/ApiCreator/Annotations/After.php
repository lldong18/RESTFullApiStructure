<?php
namespace ApiCreator\Annotations;

/**
 * Annotation to describe an after method for a controller or action.
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class After
{
    /** @var string */
    public $method;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['after'] = $this->method;
    }
}
