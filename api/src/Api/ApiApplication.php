<?php
namespace Api;

use Exception;
use Silex\Application;
use Silex\Application\SecurityTrait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Extend Silex's Application and make use of the SecurityTrait.
 *
 * @see http://silex.sensiolabs.org/doc/providers/security.html#traits
 */
class ApiApplication extends Application
{
    use SecurityTrait;

    /**
     * @param arry $config config array
     *
     * @return void
     */
    public function __construct(array $config = null)
    {
        parent::__construct();

        if (empty($config)) {
            return;
        }

        $this->addBeforeHandler();
        $this->addErrorHandler();
        $this->addAfterHandler();
    }


    private function addBeforeHandler()
    {
        echo "addBeforeHandler\n";
    }

    private function addErrorHandler()
    {
        echo "addErrorHandler\n";
    }


    private function addAfterhandler()
    {
        echo "addAfterhandler\n";
    }
}
