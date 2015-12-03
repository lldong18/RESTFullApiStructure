<?php
namespace ApiCreator\Annotations;

/**
 * Human readable name of the API controller.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Name
{
    /** @var string */
    public $name;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['name'] = $this->name;
    }
}
