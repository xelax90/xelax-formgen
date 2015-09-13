<?php
namespace XelaxFormgen;

use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;

class Module implements 
	ConsoleBannerProviderInterface,
	ConsoleUsageProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

	public function getConsoleBanner(AdapterInterface $console) {
		return 'Xelax Formgen v0.1';
	}

	public function getConsoleUsage(AdapterInterface $console) {
		return array(
			'Automatically generate forms and fieldsets for your entities',
			
			'index.php formgen' => 'Generate forms and fieldsets for all managed non-vendor entities',
			'index.php formgen --module=MyModule' => 'Generate forms and fieldsets for all managed entities in MyModule',
			'index.php formgen --entity=MyModule\Entity\Menu' => 'Generate form and fieldset for MyModule\Entity\Menu entity',
		);
	}

}
