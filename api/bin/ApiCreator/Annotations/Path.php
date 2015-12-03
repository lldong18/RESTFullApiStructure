<?php
namespace ApiCreator\Annotations;

/**
 * The url path of an action relative to the controller it belongs to.
 *
 * @Annotation
 * @Target("METHOD")
 */
class Path
{
    /** @var string */
    public $path;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['path'] = $this->path;
    }
}
