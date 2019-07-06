<?php

namespace AppBundle\Traits;

use AppBundle\Service\myClass;

/**
 * Class MyTrait
 */
trait MyTrait
{
    /**
     * @var MyClass
     */
    private $myClass;

    /**
     * MyTrait constructor.
     *
     * @param MyClass $myClass
     */
    public function __construct(MyClass $myClass)
    {
        $this->myClass = $myClass;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->myClass->myMethod();
    }
}
