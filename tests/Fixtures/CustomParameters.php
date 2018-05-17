<?php

namespace DMT\Test\Serializer\Fixtures;

use DMT\Serializer\ParameterBag;

class CustomParameters
{
    /**
     * @var ParameterBag
     *
     * @JMS\Type("ParameterBag<'DMT\Test\Serializer\Fixtures\CustomParameter'>")
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
     * @param CustomParameter $param
     */
    public function addParam(CustomParameter $param): void
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