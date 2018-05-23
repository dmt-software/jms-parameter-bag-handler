<?php

namespace DMT\Test\Serializer\Fixtures;

use DMT\Serializer\Parameter;
use DMT\Serializer\ParameterBag;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\AccessType("public_method")
 */
class DefaultParameters
{
    /**
     * @var ParameterBag
     *
     * @JMS\Type("ParameterBag")
     */
    protected $params;

    /**
     * DefaultParameters constructor.
     * @param ParameterBag $params
     */
    public function __construct(ParameterBag $params = null)
    {
        $this->params = $params ?? new ParameterBag();
    }

    /**
     * @return ParameterBag
     */
    public function getParams(): ParameterBag
    {
        return $this->params;
    }

    /**
     * @param Parameter $param
     */
    public function addParam(Parameter $param): void
    {
        $this->params[] = $param;
    }

    /**
     * @param ParameterBag $params
     */
    public function setParams(ParameterBag $params): void
    {
        $this->params = $params;
    }
}
