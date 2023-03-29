<?php

namespace DMT\Test\Serializer;

use DMT\Serializer\Parameter;
use DMT\Serializer\ParameterBag;
use DMT\Serializer\ParameterBagHandler;
use DMT\Serializer\ParameterInterface;
use DMT\Test\Serializer\Fixtures\CustomParameter;
use DMT\Test\Serializer\Fixtures\DefaultParameters;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\VisitorInterface;
use PHPUnit\Framework\TestCase;

class ParameterBagHandlerTest extends TestCase
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
     * @var array
     */
    protected $types = [
        ['name' => 'ParameterBag', 'params' => []],
        ['name' => 'ParameterBag', 'params' => [Parameter::class]],
        ['name' => 'ParameterBag', 'params' => [CustomParameter::class]],
    ];

    /**
     * @var array
     */
    protected $illegalTypes = [
        ['name' => 'ParameterBag', 'params' => [\stdClass::class]],
        ['name' => 'ParameterBag', 'params' => ['123']],
    ];


    /**
     * Test deserialization integration.
     */
    public function testDeserializeDefaultParameters()
    {
        $param = new Parameter();
        $param->setName('key');
        $param->setValue('val');
        $params = new DefaultParameters();
        $params->addParam($param);

        /** @var DefaultParameters $result */
        $result = $this->getSerializer()->deserialize('{"params":{"key":"val"}}', DefaultParameters::class, 'json');
        $current = current($result->getParams()->getArrayCopy());

        static::assertEquals($params, $result);
        static::assertSame('key', $current->getName());
        static::assertSame('val', $current->getValue());
    }

    /**
     * @dataProvider provideDeserializeParameterBagData
     *
     * @param array|\Traversable $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testDeserializeParameterBag($data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);

        $handler = new ParameterBagHandler();
        $result = $handler->deserializeParameterBag($visitor, $data, $type, DeserializationContext::create());

        array_map(
            function (ParameterInterface $parameter) use ($data) {
                static::assertArrayHasKey($parameter->getName(), $data);
                static::assertContains($parameter->getValue(), $data);
                static::assertSame($data[$parameter->getName()], $parameter->getValue());
            },
            iterator_to_array($result)
        );

        static::assertInstanceOf(ParameterBag::class, $result);
        static::assertCount(
            count((array) $data),
            $result
        );
        static::assertContainsOnly($type['params'][0] ?? Parameter::class, $result);
    }

    /**
     * @dataProvider provideDeserializeParameterBagData
     *
     * @param array|\Traversable $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testDeserializeParameterBagWithNullValues($data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);
        $context = DeserializationContext::create();

        $handler = new ParameterBagHandler();
        $result = $handler->deserializeParameterBag($visitor, $data, $type, $context);

        array_map(
            function (ParameterInterface $parameter) use ($data) {
                static::assertArrayHasKey($parameter->getName(), $data);
                static::assertContains($parameter->getValue(), $data);
                static::assertSame($data[$parameter->getName()], $parameter->getValue());
            },
            iterator_to_array($result)
        );

        static::assertInstanceOf(ParameterBag::class, $result);
        static::assertCount($data ? count($data) : 0, $result);
        static::assertContainsOnly($type['params'][0] ?? Parameter::class, $result);
    }

    /**
     * @return array
     */
    public function provideDeserializeParameterBagData(): array
    {
        $testData = [];
        foreach ($this->types as $type) {
            $testData[] = [$this->data, $type];
            $testData[] = [new \ArrayObject($this->data), $type];
            $testData[] = [null, $type];
        }

        return $testData;
    }

    /**
     * @dataProvider provideDeserializeInvalidParameterBagData
     *
     * @param mixed $data
     * @param array $type
     * @param string $message
     *
     * @throws \ReflectionException
     */
    public function testDeserializeInvalidParameterBag($data, array $type, string $message)
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage($message);

        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);

        (new ParameterBagHandler())->deserializeParameterBag($visitor, $data, $type, SerializationContext::create());
    }

    /**
     * @return array
     */
    public function provideDeserializeInvalidParameterBagData(): array
    {
        $testData = [];
        foreach ($this->data as $data) {
            if (null !== $data) {
                $testData[] = [$data, $this->types[0], 'ParameterBag only excepts an array of parameters'];
            }
        }
        foreach ($this->illegalTypes as $type) {
            $testData[] = [$this->data, $type, 'Parameter(s) must implement ' . ParameterInterface::class];
        }

        return $testData;
    }

    /**
     * Test serialization integration.
     */
    public function testSerializeDefaultParameters()
    {
        $param = new Parameter();
        $param->setName('key');
        $param->setValue('val');

        $result = $this->getSerializer()->serialize(new DefaultParameters(new ParameterBag([$param])), 'json');

        static::assertSame('{"params":{"key":"val"}}', $result);
    }

    /**
     * @dataProvider provideSerializeParameterBagData
     *
     * @param ParameterBag $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testSerializeParameterBag(ParameterBag $data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);

        $handler = new ParameterBagHandler();
        $result = $handler->serializeParameterBag($visitor, $data, $type, SerializationContext::create());

        static::assertSame($result, array_filter($this->data, 'is_scalar'));
    }

    /**
     * @dataProvider provideEmptySerializeParameterBagData
     *
     * @param ParameterBag|null $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testSerializeEmptyParameterBag(? ParameterBag $data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);

        $handler = new ParameterBagHandler();
        $result = $handler->serializeParameterBag($visitor, $data, $type, SerializationContext::create());

        static::assertNull($result);
    }

    /**
     * @dataProvider provideEmptySerializeParameterBagData
     *
     * @param ParameterBag|null $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testSerializeEmptyParameterBagWithNullValues(? ParameterBag $data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);
        $context =  SerializationContext::create()->setSerializeNull(true);

        $handler = new ParameterBagHandler();
        $result = $handler->serializeParameterBag($visitor, $data, $type, $context);

        static::assertSame([], $result);
    }

    /**
     * @return array
     */
    public function provideEmptySerializeParameterBagData(): array
    {
        $testData = [];
        foreach ($this->types as $type) {
            $testData[] = [new ParameterBag(), $type];
            $testData[] = [null, $type];
        }

        return $testData;
    }

    /**
     * @dataProvider provideSerializeParameterBagData
     *
     * @param ParameterBag $data
     * @param array $type
     *
     * @throws \ReflectionException
     */
    public function testSerializeParameterBagWithNullValues(ParameterBag $data, array $type)
    {
        /** @var VisitorInterface $visitor */
        $visitor = static::getMockForAbstractClass(VisitorInterface::class);
        $context = SerializationContext::create()->setSerializeNull(true);

        $handler = new ParameterBagHandler();
        $result = $handler->serializeParameterBag($visitor, $data, $type, $context);

        static::assertSame($result, $this->data);
    }

    /**
     * @return array
     */
    public function provideSerializeParameterBagData(): array
    {
        $testData = [];
        foreach ($this->types as $type) {
            $testData[] = [$this->getParameterBag(), $type];
            $testData[] = [$this->getParameterBag(CustomParameter::class), $type];
        }

        return $testData;
    }

    /**
     * @param string $parameterClass
     * @return ParameterBag
     */
    protected function getParameterBag(string $parameterClass = Parameter::class): ParameterBag
    {
        return new ParameterBag(
            array_map(
                function ($key, $val) use ($parameterClass) {
                    /** @var ParameterInterface $parameter */
                    $parameter = new $parameterClass();
                    $parameter->setName($key);
                    $parameter->setValue($val);

                    return $parameter;
                },
                array_keys($this->data),
                $this->data
            )
        );
    }

    /**
     * @return Serializer
     */
    protected function getSerializer(): Serializer
    {
        return SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new ParameterBagHandler());
            })
            ->build();
    }
}
