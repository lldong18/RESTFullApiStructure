<?php
namespace ApiCreator\Annotations;

/**
 * An input parameter that can be passed in the request that should be
 * validated.
 *
 * @Annotation
 * @Target("METHOD")
 */
class Validate
{
    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var bool */
    public $optional;

    /** @var mixed */
    public $default;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        if (!isset($array['validate'])) {
            $array['validate'] = array();
        }

        $array['validate'][] = array(
            'name' => $this->name,
            'type' => $this->type,
            'optional' => $this->optional ? true : false,
            'default' => $this->default,
        );
    }
}
