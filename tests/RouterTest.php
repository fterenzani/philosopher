<?php

use Socrate\Router;
use Pimple\Container;

class RouterTest extends PHPUnit_Framework_TestCase
{

    /**
     * - Container must be the first argument of any Callback
     * - Callback must be called
     * - $_GET superglobal must be populated
     */
    public function testCallbacks()
    {

        $pimple = new Container;
        $pimple['phpUnit'] = $this;
        $router = new Router($pimple);

        $router->urls[] = ['#^test1$#', 'test1'];  	
        $router->urls[] = ['#^test2$#', 'Test2'];  	
        $router->urls[] = ['#^test3$#', 'Test3@handleTest'];  	
        $router->urls[] = ['#^test/(?<test>\d+)$#', 'test4.php'];

        $this->expectOutputString('foo1foo2foo3foo4');

        // Callback
        $router->dispatch('test1');
        // Class
        $router->dispatch('test2');
        // Method
        $router->dispatch('test3');
        // require file
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $router->dispatch('test/4');

        // Pupulation of the $_GET superglobal
    	$this->assertEquals('4', $_GET['test']);

    }

    public function testGetPath()
    {

        $pimple = new Container;
        $router = new Router($pimple);

        $router->urls['archive'] = ['#^(?<year>\d{4})(/(?<month>\d{2}))?$#', function(){}];
    	
        // Array, object and class handling
    	$path = $router->getPath('archive', ['year' => 2015, 'month' => 12]);
    	$this->assertEquals('2015/12', $path);
    	
       	$path = $router->getPath('archive', (object)['year' => 2015, 'month' => 12]);
    	$this->assertEquals('2015/12', $path);

       	$path = $router->getPath('archive', new Archive);
    	$this->assertEquals('2015/12', $path);

        // Optional varable removed
        $path = $router->getPath('archive', ['year' => 2015]);
        $this->assertEquals('2015', $path);

        // Rules with no params
        $router->urls['no-params'] = ['#^no-params-path$#', function(){}];
        $path = $router->getPath('no-params');
        $this->assertEquals('no-params-path', $path);


    }

}


function test1($container) 
{
    echo 'foo1';
    $container['phpUnit']->assertTrue(true);
}

class Test2 
{
	function __construct($container) 
	{
        echo 'foo2';
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
        echo 'foo3';
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