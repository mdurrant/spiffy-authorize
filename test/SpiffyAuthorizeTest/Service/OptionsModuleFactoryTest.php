<?php

namespace SpiffyAuthorizeTest\Service;

use SpiffyAuthorize\Service\OptionsModuleFactory;
use Zend\ServiceManager\ServiceManager;

class OptionsModuleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testModuleOptionsInstanceReturned()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Configuration', array(
            'spiffy_authorize' => array(
                'default_role' => 'foo'
            )
        ));

        $factory = new OptionsModuleFactory();
        $options = $factory->createService($serviceManager);
        $this->assertInstanceOf('SpiffyAuthorize\Options\ModuleOptions', $options);
        $this->assertEquals('foo', $options->getDefaultRole());
    }
}