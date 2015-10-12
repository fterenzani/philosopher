<?php

use Socrate\Http404;

class Http404Test extends PHPUnit_Framework_TestCase
{

    public function testIsException()
    {
        $error = new Http404;
        $this->assertInstanceOf('Exception', $error);
    }

}