<?php
namespace ApiCreator\Annotations;

/**
 * Describe an assert applied to an action.
 * Currently required for silex asserts.
 *
 * ie.
 * /photo/{id}
 *
 * Would require an assert on the id like
 * ->assert('id', '\d+')
 *
 * @Annotation
 * @Target("METHOD")
 */
class Assert
{
    /** @var string */
    public $name;

    /**
     * This is the regex WITHOUT the delimiters
     * @var string
     **/
    public $regex;

    /**
     * {@inheritDoc}
     */
    public function injectData(&$array)
    {
        if (!isset($array['assert'])) {
            $array['assert'] = array();
        }

        $array['assert'][] = array(
            'name' => $this->name,
            'regex' => $this->regex,
        );
    }
}
