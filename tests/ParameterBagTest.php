<?php

namespace DMT\Test\Serializer;

use DMT\Serializer\Parameter;
use DMT\Serializer\ParameterBag;
use DMT\Serializer\ParameterInterface;
use PHPUnit\Framework\TestCase;

class ParameterBagTest extends TestCase
{
    /**
     * @var array
     */
    protected $data = [
        'text' => 'lorum ipsum',
        'number' => 132,
        'percentage' => 66.67,
        'deleted' => true,
        'empty' => null
    ];

    /**
     * Test all elements in parameter bag.
     */
    public function testOffsetGet()
    {
        $parameterBag = $this->getTestParameterBag();

        $i = 0;
        foreach ($this->data as $key => $value) {
            $parameter = $parameterBag[$key];

            static::assertArrayHasKey($i, $parameterBag);
            static::assertArrayHasKey($key, $parameterBag);
            static::assertSame($parameterBag[$key], $parameterBag[$i++]);
            static::assertInstanceOf(ParameterInterface::class, $parameterBag[$key]);
            static::assertSame($key, $parameter->getName());
            static::assertSame($value, $parameter->getValue());
        }
        static::assertCount(count($this->data), $parameterBag);
    }

    /**
     * Test null is returned for missing parameters.
     */
    public function testOffsetGetUnknownParameter()
    {
        $parameterBag = new ParameterBag();

        static::assertNull($parameterBag[0]);
        static::assertNull($parameterBag['foo']);
    }

    /**
     * @dataProvider provideValidParameterBagParameters
     * @param ParameterInterface $parameter
     */
    public function testOffsetSet(ParameterInterface $parameter)
    {
        $parameterBag = new ParameterBag();
        $parameterBag[] = $parameter;

        static::assertCount(1, $parameterBag);
        static::assertSame($parameterBag[0], $parameter);
    }

    /**
     * @return \Generator
     */
    public function provideValidParameterBagParameters(): \Generator
    {
        foreach ($this->getTestParameterBag() as $parameter) {
            yield [$parameter];
        }
    }

    /**
     * Test offset set sets only parameters with a unique name.
     */
    public function testPreventDoubleOffsetEntries()
    {
        $params = [];
        $params[0] = new Parameter();
        $params[0]->setName('foo');
        $params[0]->setValue('bar');
        $params[1] = new Parameter();
        $params[1]->setName('foo');
        $params[1]->setValue('baz');

        $parameterBag = new ParameterBag($params);

        static::assertCount(1, $parameterBag);
        static::assertSame($params[0], $parameterBag[0]);
        static::assertContains($params[0], $parameterBag);
        static::assertNotContains($params[1], $parameterBag);
    }

    /**
     * Test adding a wrong parameter to parameter bag.
     */
    public function testSetIllegalParameter()
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Parameter(s) in ParameterBag must implement DMT\Serializer\ParameterInterface');

        $parameterBag = new ParameterBag();
        $parameterBag[] = new \ArrayObject(['key' => 'foo', 'value' => 'bar']);
    }

    /**
     * Test offset exists.
     */
    public function testOffsetExists()
    {
        $parameterBag = $this->getTestParameterBag();

        foreach (array_keys($this->data) as $i => $key) {
            static::assertTrue(isset($parameterBag[$i]));
            static::assertTrue(isset($parameterBag[$key]));
        }
    }

    /**
     * Test unset a parameter for parameter bag.
     */
    public function testOffsetUnset()
    {
        $parameterBag = $this->getTestParameterBag();

        for ($i = 0; $i < count($parameterBag); $i++) {
            unset($parameterBag[$i]);
            static::assertArrayNotHasKey($i, $parameterBag);
        }
    }

    /**
     * Test unset a parameter for parameter bag using the parameter's name.
     */
    public function testOffsetUnsetByParameterName()
    {
        $parameterBag = $this->getTestParameterBag();

        foreach (array_keys($this->data) as $key) {
            unset($parameterBag[$key]);
            static::assertArrayNotHasKey($key, $parameterBag);
        }
    }

    /**
     * @return ParameterBag
     */
    protected function getTestParameterBag(): ParameterBag
    {
        return
            new ParameterBag(
                array_map(
                    function ($key, $val) {
                        $param = new Parameter();
                        $param->setName($key);
                        $param->setValue($val);

                        return $param;
                    },
                    array_keys($this->data),
                    $this->data
                )
            );
    }
}
