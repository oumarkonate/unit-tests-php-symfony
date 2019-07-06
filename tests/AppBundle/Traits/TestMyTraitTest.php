<?php

namespace Tests\AppBundle\Traits;

use AppBundle\Traits\MyTrait;
use AppBundle\Service\MyClass;
use PHPUnit\Framework\TestCase;

class TestMyTraitTest extends TestCase
{
    /**
     * Test method
     */
    public function testMethod()
    {
        $myClass = $this->createMock(MyClass::class);
        $myClass->expects($this->once())
            ->method('myMethod')
            ->willReturn('some-string');

        $myTrait = $this->getObjectForTrait(MyTrait::class, [$myClass]);

        $this->assertSame('some-string', $myTrait->method());
    }
}
