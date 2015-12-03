<?php
namespace ApiCreator\Annotations;

/**
 * Annotation to secure a controller class or a controller method
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Secure
{
    /** @var array */
    public $secure;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['secure'] = $this->secure;
    }
}
