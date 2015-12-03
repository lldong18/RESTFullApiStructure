<?php
namespace ApiCreator\Annotations;

/**
 * A readable description to give a controller or action.
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Description
{
    /** @var string */
    public $description;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['description'] = $this->description;
    }
}
