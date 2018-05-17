<?php

namespace DMT\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\VisitorInterface;
use phpDocumentor\Reflection\Types\Object_;

class ParameterBagHandler implements SubscribingHandlerInterface
{
    /** @codeCoverageIgnoreStart */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'ParameterBag',
                'method' => 'serializeParameterBag',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'ParameterBag',
                'method' => 'deserializeParameterBag',
            ],
        ];
    }
    /** @codeCoverageIgnoreEnd */

    /**
     * @param JsonSerializationVisitor|VisitorInterface $visitor
     * @param ParameterBag|null $data
     * @param array $type
     * @param Context $context
     *
     * @return array|null
     * @throws RuntimeException
     */
    public function serializeParameterBag(VisitorInterface $visitor, ? ParameterBag $data, array $type, Context $context):? array
    {
        $result = [];
        if ($data === null) {
            return $context->shouldSerializeNull() ? $result : null;
        }

        foreach ($data as $parameter) {
            if (!$context->shouldSerializeNull() && $parameter->getValue() === null) {
                continue;
            }
            $result[$parameter->getName()] = $parameter->getValue();
        }

        return count($result) || $context->shouldSerializeNull() ? $result : null;
    }

    /**
     * Deserialize elements into a bag of parameters.
     *
     * @param JsonDeserializationVisitor|VisitorInterface $visitor
     * @param array $data
     * @param array $type
     * @param Context $context
     *
     * @return ParameterBag
     * @throws RuntimeException
     */
    public function deserializeParameterBag(VisitorInterface $visitor, $data, array $type, Context $context): ParameterBag
    {
        $result = new ParameterBag();
        if (null === $data) {
            return $result;
        }
        if (!is_iterable($data)) {
            throw new RuntimeException('ParameterBag only excepts an array of parameters');
        }

        /** @var ParameterInterface $parameter */
        $parameterType = $this->getParameterType($type);
        $parameter = unserialize(sprintf('O:%d:"%s":0:{}', strlen($parameterType), $parameterType));

        foreach ($data as $key => $value) {
            if (!$context->shouldSerializeNull() && $value === null) {
                continue;
            }
            $instance = clone $parameter;
            $instance->setName($key);
            $instance->setValue($value);

            $result[] = $instance;
        }

        return $result;
    }

    /**
     * Get the class name of the object to store in parameter bag.
     *
     * @param array $type
     *
     * @return string The class name for the parameters.
     * @throws RuntimeException
     */
    protected function getParameterType(array $type): string
    {
        if (empty($type['params'])) {
            return Parameter::class;
        }
        if (!class_exists($type['params'][0]) || !is_subclass_of($type['params'][0], ParameterInterface::class)) {
            throw new RuntimeException('Parameter(s) must implement ' . ParameterInterface::class);
        }

        return $type['params'][0];
    }
}
