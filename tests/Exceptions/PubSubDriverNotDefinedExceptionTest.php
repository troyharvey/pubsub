<?php

namespace GenTux\PubSub\Exceptions;

class PubSubDriverNotDefinedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_has_driver_not_defined_exception()
    {
        $this->setExpectedException(PubSubDriverNotDefinedException::class);
        throw new PubSubDriverNotDefinedException();
    }
}
