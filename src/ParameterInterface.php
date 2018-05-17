<?php

namespace DMT\Serializer;

interface ParameterInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return bool|float|int|string
     */
    public function getValue();

    /**
     * @param bool|float|int|string $value
     */
    public function setValue($value);
}