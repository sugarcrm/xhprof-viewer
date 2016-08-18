<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\DependencyInjection;

class Container
{
    /**
     * Container of objects in format [alias => object, ...]
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Injections of class methods in format [class name => [method name => injection, ...], ...]
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Used to prevent a closure of a class on itself
     *
     * @var array
     */
    protected $chain = array();

    /**
     * Add instance to container
     *
     * @param string $name Alias for object in the container
     * @param $instance
     * @throws \Exception
     */
    public function set($name, $instance)
    {
        if (!is_object($instance)) {
            throw new \Exception(sprintf('Only object can be set to DI container. %s is given', gettype($instance)));
        }
        $this->instances[$name] = $instance;
    }

    /**
     * Retrieve an instance from the container by key
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function get($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        return $this->instantiate($name);
    }

    /**
     * Create an object with dependencies by class name
     *
     * @param $className
     * @return mixed
     * @throws \Exception
     */
    protected function instantiate($className)
    {
        if (isset($this->chain[$className])) {
            $chain = array_keys($this->chain);
            $this->chain = [];
            throw new \Exception(
                sprintf(
                    'Cannot instantiate %s. It depends on itself. Chain: %s',
                    $chain[0],
                    implode('->', $chain)
                )
            );
        }

        $this->chain[$className] = true;
        $instance = $this->createObject($className);

        $dependencies = $this->getDependencies($className);
        if (!empty($dependencies)) {
            foreach ($dependencies as $method => $dependency) {
                $instance->$method($this->get($dependency));
            }
        }

        // last object in chain was just initialized, remove it from chain
        unset($this->chain[$className]);
        return $instance;
    }

    /**
     * Return a new instance of the class
     *
     * @param $className
     * @return mixed
     */
    protected function createObject($className)
    {
        return new $className();
    }

    /**
     * Return dependency injections for class methods
     *
     * @param $className
     * @return array
     */
    protected function getDependencies($className)
    {
        if (!isset($this->cache[$className])) {
            $this->cache[$className] = $this->buildDependenciesMeta($className);
        }
        return $this->cache[$className];
    }

    /**
     * Prepare and return dependency metadata for class methods using annotations
     *
     * @param $className
     * @return array
     * @throws \Exception
     */
    protected function buildDependenciesMeta($className)
    {
        $reflection = new \ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodsInjection = [];

        foreach ($methods as $method) {
            if (substr($method->getName(), 0, 3) == 'set') {
                $docComment = $method->getDocComment();
                if (strpos($docComment, '@inject') === false) {
                    continue;
                }
                $methodParams = $method->getParameters();
                if (count($methodParams) != 1) {
                    throw new \Exception(
                        sprintf(
                            'Setter %s in %s must have exactly one parameter',
                            $method->getName(),
                            $reflection->getName()
                        )
                    );
                }
                $methodsInjection[$method->getName()] = $this->parseDocComment($docComment);
            }
        }
        return $methodsInjection;
    }

    /**
     * Parse doc comment to retrieve dependency name
     *
     * @param $comment
     * @return mixed
     * @throws \Exception
     */
    protected function parseDocComment($comment)
    {
        preg_match('/@inject\s+([A-Za-z_0-9\\\]+)\s?/', $comment, $result);
        if (!isset($result[1])) {
            throw new \Exception('@inject annotation has wrong format');
        }
        return $result[1];
    }
}
