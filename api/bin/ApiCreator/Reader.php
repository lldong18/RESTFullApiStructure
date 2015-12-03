<?php
namespace ApiCreator;

use ApiCreator\Util\DirectoryTraverser;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * This is a class that parses controllers to generate info about an API.
 *
 * It assumes the following structure:
 * <namespace>/<controller-class>
 * <namespace>/<sub-dir>/<controller-class>
 *
 * The controller classes should be autoloaded in PSR-0 format.
 * For example:
 * filepath -> Controllers/Account/PhotoController.php
 * class        -> Controllers\Account\PhotoController
 *
 * Some parts are defined to be used with silex, such as:
 * - path (ie. /myUrl/{someId})
 * - bind (ie. ->bind('myName') for use with its UrlGenerator class)
 * - assert (ie. ->assert('someId', '\d+'))
 * TODO
 * - remove silex specific stuff
 * - maybe have hooks to hook in specific stuff
 *     - ie. when parsing controller, look for "bind"
 */
class Reader
{
    // regex to check if a file is a controller based on its filename
    const CONTROLLER_FILENAME_REGEX = '/Controller\.php$/';

    // path to the controller files
    private $controllerPath;
    // base namespace of the controllers
    private $namespace;
    // instance of AnnotationReader
    private $annotationReader;

    /**
     * @param string $controllerPath path to the controllers
     * @param string $namespace            namespace of the controllers
     */
    public function __construct($controllerPath, $namespace)
    {
        $this->controllerPath = $controllerPath;
        $this->namespace = $namespace;
        $this->annotationReader = new AnnotationReader();

        // need to let the annotation registry know where to find the annotations
        AnnotationRegistry::registerAutoloadNamespace('ApiCreator\Annotations', dirname(__DIR__));
    }

    /**
     * Return the api definition in an array.
     *
     * @return array
     */
    public function getApi()
    {
        $api = array(
            'namespace' => $this->namespace,
            'controllers' => array()
        );

        $controllerFiles = DirectoryTraverser::findFiles($this->controllerPath, self::CONTROLLER_FILENAME_REGEX);
        foreach ($controllerFiles as $filename) {
            $className = $this->pathToClassName($filename);
            $controller = $this->loadFromClassName($className);
            $api['controllers'][] = $controller;
        }

        return $api;
    }

    /**
     * Convert a controller's full filename into a full PHP class name.
     *
     * @param string $filename path to the controller file
     *
     * @return string
     */
    private function pathToClassName($filename)
    {
        // strip the basepath from the controller
        $controller = str_replace($this->controllerPath, '', $filename);
        // remove trailing slashes
        $controller = trim($controller, DIRECTORY_SEPARATOR);
        // remove the file extension
        $controller = preg_replace('/\.php$/', '', $controller);
        // change directory separators to namespace ones
        $controller = str_replace(DIRECTORY_SEPARATOR, '\\', $controller);

        return "{$this->namespace}\\$controller";
    }

    /**
     * Read API data from a controller class name.
     * Uses annotations to figure out the controller's details.
     *
     * @param string $className full class name of the controller
     *
     * @return array
     *
     * ie.
     * Controller format:
     * array(
     *     'className'     => 'string',             // full namespace + classname
     *     'name'                => 'string',             // readable name
     *     'description' => 'string',             // description of the controller
     *     'before'            => 'string|false', // name of the before method to call
     *     'after'             => 'string|false', // name of the after method to call
     *     'actions'         => array(                    // actions associated with this controller
     *         ... (see below Action format)
     *     ),
     *     'exceptions'    => array(                    // exceptions to handle
     *         ... (see below Exception format)
     *     ),
     * )
     *
     * Action format:
     * array(
     *     'description' => 'string', // description of the action
     *     'method'            => 'string', // HTTP method
     *     'methodName'    => 'string', // name of the class method to call
     *     'path'                => 'string', // path relative to the controller
     *     'bind'                => 'string', // for Silex urlGenerator
     *     'success'         => array(        // success response details
     *         'code' => 'int',                 // status code for a successful response
     *     ),
     *     'before'            => 'string', // name of the before method to call
     *     'after'             => 'string', // name of the after method to call
     *     'validate'        => array(        // request parameters to validate
     *         ... (see below Validate format)
     *     ),
     *     'assert'            => array(        // url parameters to validate
     *         ... (see below Assert format)
     *     ),
     *     'exceptions'    => array(                    // exceptions to handle
     *         ... (see below Exception format)
     *     ),
     * )
     *
     * Notes:
     * "before" methods are going to be called in the following order:
     * - controller's before
     * - action's before
     *
     * Notes:
     * "after" methods are going to be called in the following order:
     * - action's after
     * - controller's after
     */
    private function loadFromClassName($className)
    {
        $rClass = new \ReflectionClass($className);
        $controller = array(
            'className' => $className
        );
        $classAnnotations = $this->annotationReader->getClassAnnotations($rClass);
        $this->extractData($classAnnotations, $controller);

        $actions = array();
        $rMethods = $rClass->getMethods();
        foreach ($rMethods as $rMethod) {
            $data = array(
                'methodName' => $rMethod->getName()
            );
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($rMethod);
            $this->extractData($methodAnnotations, $data);
            if ($this->isValidAction($data)) {
                $actions[] = $data;
            }
        }

        $controller['actions'] = $actions;

        return $controller;
    }

    /**
     * Calls annotation's injectData method to copy the annotation's data into
     * the array in the proper format.
     *
     * @param array $annotations An array of annotation objects
     * @param array &$array            Array to extract data to
     */
    private function extractData($annotations, &$array)
    {
        foreach ($annotations as $annotation) {
            if (!method_exists($annotation, 'injectData')) {
                throw new \RuntimeException(sprintf('%s::injectData() does not exist', get_class($annotation)));
            }
            $annotation->injectData($array);
        }
    }

    /**
     * Determine if an array of data constitutes a valid action.
     *
     * @param array $data annotation data gathered from a ReflectionMethod
     *
     * @return bool
     */
    private function isValidAction(array $data)
    {
        $requires = array(
            'description',
            'method',
            'methodName',
            'path'
        );

        foreach ($requires as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        return true;
    }
}
