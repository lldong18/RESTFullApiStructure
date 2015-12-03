<?php
namespace ApiCreator\Annotations;

/**
 * Describes a success when an action is called.
 *
 * @Annotation
 * @Target("METHOD")
 */
class Success
{
    /** @var int */
    public $code;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        $array['success'] = array(
            'code' => $this->code
        );
    }
}
