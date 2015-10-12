<?php

use Socrate\Router;
use Pimple\Container;

class RouterTest extends PHPUnit_Framework_TestCase
{


    public function testCallbacks()
    {

        $pimple = new Container;
        $pimple['phpUnit'] = $this;
        $router = new Router($pimple);

        $router->urls[] = ['#^test1$#', 'test1'];  	
        $router->urls[] = ['#^test2$#', 'Test2'];  	
        $router->urls[] = ['#^test3$#', 'Test3@handleTest'];  	
        $router->urls[] = ['#^test/(?<test>\d+)$#', 'test4.php'];

        $router->dispatch('test1');
        $router->dispatch('test2');

        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $router->dispatch('test3');

        $router->dispatch('test/4');

    	$this->assertEquals('4', $_GET['test']);

    }

    public function testGetPath()
    {

        $pimple = new Container;
        $router = new Router($pimple);

    	$router->urls['archive'] = ['#^(?<year>\d{4})(/(?<month>\d{2}))?$#', function(){}];
    	
    	$path = $router->getPath('archive', ['year' => 2015, 'month' => 12]);
    	$this->assertEquals('2015/12', $path);
    	
       	$path = $router->getPath('archive', (object)['year' => 2015, 'month' => 12]);
    	$this->assertEquals('2015/12', $path);

       	$path = $router->getPath('archive', new Archive);
    	$this->assertEquals('2015/12', $path);

    }

}


function test1($container) 
{
	$container['phpUnit']->assertTrue(true);
}

class Test2 
{
	function __construct($container) 
	{
		$container['phpUnit']->assertTrue(true);
	}
}

class Test3 
{
	function __construct($container) 
	{
		$this->container = $container;
	}

	function handleTest() 
	{
		$this->container['phpUnit']->assertTrue(true);
	}
}

class Archive
{
	function getYear()
	{
		return 2015;
	}

	function getMonth()
	{
		return 12;
	}
}