<?php

namespace SpiffyAuthorize\Service;

use SpiffyAuthorize\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractInstanceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \SpiffyAuthorize\Options\ModuleOptions $options */
        $options   = $serviceLocator->get('SpiffyAuthorize\Options\ModuleOptions');
        $instances = [];

        foreach ($this->getInstances($options) as $config) {
            $instance = $this->get($serviceLocator, $config['name']);
            $options  = isset($config['options']) ? $config['options'] : [];

            foreach ($options as &$value) {
                $value = $this->get($serviceLocator, $value, false);
            }

            $instance->setFromArray($options);

            $instances[] = $instance;
        }

        return $instances;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param bool $create
     * @return object
     */
    protected function get(ServiceLocatorInterface $serviceLocator, $name, $create = true)
    {
        if (is_string($name) && $serviceLocator->has($name)) {
            return $serviceLocator->get($name);
        }
        if ($create) {
            return new $name;
        }
        return $name;
    }

    /**
     * @param ModuleOptions $options
     * @return array
     */
    abstract protected function getInstances(ModuleOptions $options);
}