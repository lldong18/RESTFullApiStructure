<?php
namespace ApiCreator\Exporters;

use ApiCreator\Util\DirectoryCreator;
use Symfony\Component\Yaml\Yaml;

/**
 * Uses twig for template parsing.
 */
class Silex extends AbstractExporter
{
    /** @var string directory to export to */
    protected $exportDir;
    /** @var string base namespace to put the new classes in */
    protected $newNamespace;
    /** @var Twig template renderer instance */
    protected $twig;
    /** @var string root controller, has a special url '/' */
    protected $rootController;

    /** @var array of routes */
    private $routes = array();

    /**
     * {@inheritdoc}
     */
    protected function validateSetup()
    {
        if (!$this->exportDir) {
            throw new \InvalidArgumentException('Missing exportDir');
        }

        // remove trailing directory separators
        $this->exportDir = rtrim($this->exportDir, '/');

        if (!$this->newNamespace) {
            throw new \InvalidArgumentException('Missing newNamespace');
        }

        // remove trailing namespace separators
        $this->newNamespace = rtrim($this->newNamespace, '\\');

        $twigLoader = new \Twig_Loader_Filesystem(__DIR__ . '/Silex/templates');
        $this->twig = new \Twig_Environment(
            $twigLoader,
            array(
                'autoescape' => false // make all the text raw, we are writing php files...
            )
        );

        if (!$this->twig) {
            throw new \InvalidArgumentException('Missing twig');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (isset($this->api['controllers'])) {
            foreach ($this->api['controllers'] as $controller) {
                $this->renderController($controller);
            }
        }

        $this->exportRoutes();
    }

    /**
     * Write a Silex\ControllerCollection class file based on the api definition
     *
     * @param array $controller array representation of controller
     */
    private function renderController($controller)
    {
        // Get the actual class's name, minus the namespace
        // ie. <old-namespace>\Account\PhotosController -> PhotosController
        $classParts = explode('\\', $controller['className']);
        $class = array_pop($classParts);

        // Get the relative class without the namespace
        // ie. <old-namespace>\Account\PhotosController -> Account\PhotosController
        $relativeClass = str_replace($this->api['namespace'] . '\\', '', $controller['className']);

        // Get the new namespaced controller class
        // ie. <old-namespace>\Account\PhotosController -> <new-namespace>\Account\PhotosController
        $controllerClass = $this->newNamespace . '\\' . $relativeClass;

        // strip classname and remaining namespace separator
        // ie. <new-namespace>\Account\PhotosController -> <new-namespace>\Account
        // note: need to triple backslash for the regex parser
        $namespace = preg_replace('/\\\(\w+)$/', '', $controllerClass);

        // file to write follows PSR-0 standard
        // change namespace separators to directory separators and add file extnesion
        // ie. <new-namespace>\Account\PhotosController -> <new-namespace>/Account/PhotosController.php
        $fileToWrite = str_replace('\\', '/', $controllerClass) . '.php';

        // This is the url path relative to the root
        // ie. Account\PhotosController -> /account/photos
        if ($relativeClass === $this->rootController) {
            $apiPath = '/';
        } else {
            $apiPath = str_replace('\\', '/', $relativeClass);
            // strip "Controller" from the classname for the url
            // TODO make this an option
            $apiPath = preg_replace('/Controller$/', '', $apiPath);
            $apiPath = '/' . strtolower($apiPath);
        }

        $this->addRoute($apiPath, $controllerClass);

        $data = array(
            'namespace' => $namespace,
            'controllerName' => $class,
            'apiPath' => $apiPath,
        );
        $data = array_merge($controller, $data);

        if (isset($data['before'])) {
            $data['beforeCall'] = $this->generateMethodCall($controller['className'], $data['before']);
        }
        if (isset($data['after'])) {
            $data['afterCall'] = $this->generateMethodCall($controller['className'], $data['after']);
        }

        foreach ($data['actions'] as $key => $action) {
            $data['actions'][$key] = $this->processAction($action, $controller);
        }

        $template = $this->twig->render('controller.php.twig', $data);

        $this->writeFile($this->exportDir . '/' . $fileToWrite, $template);
    }

    /**
     * Process actions for the template handler.
     *
     * @param array $action         action data
     * @param array $controller controller data
     *
     * @return array
     */
    private function processAction($action, $controller)
    {
        $params = array();

        // create validation arrays for each action
        if (isset($action['validate'])) {
            $action['validationArray'] = var_export($this->generateValidationArray($action['validate']), 1);
        } else {
            $action['validationArray'] = 'array()';
        }

        // generate the parameter list for action method calls
        // also pass in an array of validated parameters
        $action['methodCall'] = $this->generateMethodCall($controller['className'], $action['methodName']);

        // generate the parameter list for before method calls
        if (isset($action['before'])) {
            $action['beforeCall'] = $this->generateMethodCall($controller['className'], $action['before']);
        }

        // generate the parameter list for after method calls
        if (isset($action['after'])) {
            $action['afterCall'] = $this->generateMethodCall($controller['className'], $action['after']);
        }

        if (isset($action['convert'])) {
            foreach ($action['convert'] as $index => $convert) {
                $action['convert'][$index]['call'] = $this->generateMethodCall(
                    $controller['className'],
                    $convert['method']
                );
                $params[] = $convert['name'];
            }
        }

        if (isset($action['assert'])) {
            foreach ($action['assert'] as $assert) {
                $params[] = $assert['name'];
            }
        }

        $params = array_unique($params);

        $action['params'] = $params;

        return $action;
    }

    /**
     * Write a file, creates dir if needed.
     *
     * @param string $fileToWrite full path to the file write
     * @param string $contents        contents of the file
     */
    private function writeFile($fileToWrite, $contents)
    {
        $dirName = dirname($fileToWrite);
        if (!DirectoryCreator::create($dirName)) {
            throw new \RuntimeException("Unable to create directory: $dirName");
        }

        if (file_put_contents($fileToWrite, $contents) === false) {
            throw new \RuntimeException(sprintf("Unable to write file: %s", $fileToWrite));
        }
    }

    /**
     * Turn a validate array into the format for the validator.
     * This is a php array, easier to do it from here instead of writing a
     * php array with a template parser.
     *
     * ie.
     * array(
     *     array(
     *         'name' => 'blah',
     *         'type' => 'int',
     *         'optional' => true,
     *         'default' => 1
     *     ),
     *     ...
     * )
     *
     * into:
     * array(
     *     'blah' => array(
     *         'type' => 'int',
     *         'optional' => true,
     *         'default' => 1
     *     ),
     *     ...
     * )
     *
     * @param array $validate
     *
     * @return array
     */
    private function generateValidationArray($validate)
    {
        $validationArray = array();
        foreach ($validate as $v) {
            $key = $v['name'];
            $validationArray[$key]['type'] = $v['type'];
            if ($v['optional']) {
                $validationArray[$key]['optional'] = true;
            }
            if ($v['default'] !== null) {
                $validationArray[$key]['default'] = $v['default'];
            }
        }

        return $validationArray;
    }

    /**
     * Generate the method call.
     * This function handles the special app and request variables.
     * If a parameter specifies to use those classes, we should just pass in
     * $app or $request.
     *
     * @param string $className    Name of the controller class to call
     * @param string $methodName Method of the controller to call
     *
     * @return string
     */
    private function generateMethodCall($className, $methodName)
    {
        $map = array(
            'Symfony\Component\HttpFoundation\Request' => '$request',
            'Symfony\Component\HttpFoundation\Response' => '$response',
            'Silex\Application' => '$app',
            'Symfony\Component\Security\Core\User\UserInterface' => '$app->user()',
        );

        // figure out the parameters to call based on the action's method signature
        $rMethod = new \ReflectionMethod($className, $methodName);
        $rParams = $rMethod->getParameters();
        $params = array();
        foreach ($rParams as $p) {
            $name = false;
            if ($c = $p->getClass()) {
                $class = $c->getName();
                if (isset($map[$class])) {
                    $name = $map[$class];
                }
            }

            // special value, maybe use symfony parameterbag or something?
            if ($p->getName() === 'params') {
                $name = '$validParameters';
            }

            if (!$name) {
                $name = '$' . $p->getName();
            }

            $params[] = $name;
        }

        return "\\{$className}::{$methodName}(" . implode(', ', $params) . ")";
    }

    /**
     * Add to list of routes
     *
     * @param string $apiPath                 the relative url path
     * @param string $controllerClass the controller class the path maps to
     */
    private function addRoute($apiPath, $controllerClass)
    {
        $this->routes[$apiPath] = $controllerClass;
    }

    /**
     * Export list of routes
     *
     * @return void
     */
    private function exportRoutes()
    {
        if (count($this->routes) === 0) {
            return;
        }

        $this->routes = $this->sortRoutes($this->routes);

        $fileToWrite = $this->exportDir . '/routes.yml';
        if (file_put_contents($fileToWrite, Yaml::dump(array('routes' => $this->routes))) === false) {
            throw new \RuntimeException(sprintf("Unable to write file: %s", $fileToWrite));
        }
    }

    /**
     * Sort by number of parts in a path
     *
     * More specific routes need to come first when routing.
     * ie. /account should come before /, otherwise the / will always match
     *
     * @param array $arr key/value pairs of path => controller
     *
     * @return array
     */
    public function sortRoutes($arr)
    {
        $array = $arr;

        $sorter = function ($a, $b) {
            $a = trim($a, '/');
            $b = trim($b, '/');

            $aValue = 0;
            $bValue = 0;

            if ($a) {
                $aValue = count(explode('/', $a));
            }

            if ($b) {
                $bValue = count(explode('/', $b));
            }

            if ($aValue === $bValue) {
                return strcmp($a, $b);
            }

            return $aValue < $bValue ? -1 : 1;
        };

        uksort($array, $sorter);

        return array_reverse($array);
    }
}
