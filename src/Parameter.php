<?php

namespace DMT\Serializer;

class Parameter implements ParameterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool|float|int|string
     */
    protected $value;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return bool|float|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool|float|int|string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}