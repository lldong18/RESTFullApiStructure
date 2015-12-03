<?php
namespace ApiCreator\Annotations;

/**
 * Describes the name to bind an action to.
 * This is used by the silex url generator.
 *
 * ie. ->bind('hello.there')
 *
 * @Annotation
 * @Target("METHOD")
 */
class Bind
{
    /** @var string */
    public $bind;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['bind'] = $this->bind;
    }
}
