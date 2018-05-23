<?php

namespace DMT\Serializer;

/**
 * Class ParameterBag
 *
 * @package DMT\Serializer
 */
class ParameterBag extends \ArrayObject
{
    /**
     * ParameterBag constructor.
     *
     * @param ParameterInterface[] $parameters
     */
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $parameter) {
            $this[] = $parameter;
        }
    }

    /**
     * Whether a offset exists.
     *
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (gettype($offset) === 'string') {
            $offset = $this->getOffsetFromName($offset);
        }

        if (gettype($offset) === 'integer') {
            return parent::offsetExists($offset);
        }

        return false;
    }

    /**
     * Offset to retrieve.
     *
     * @param string|int $offset
     * @return ParameterInterface|null
     */
    public function offsetGet($offset): ?ParameterInterface
    {
        if (gettype($offset) === 'string') {
            $offset = $this->getOffsetFromName($offset);
        }

        if (gettype($offset) === 'integer' && parent::offsetExists($offset)) {
            return parent::offsetGet($offset);
        }

        return null;
    }

    /**
     * Offset to set.
     *
     * @param string|int|null $offset
     * @param ParameterInterface $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof ParameterInterface) {
            throw new \RuntimeException('Parameter(s) in ParameterBag must implement ' . ParameterInterface::class);
        }

        if (!$this->offsetExists($value->getName())) {
            parent::offsetSet(null, $value);
        }
    }

    /**
     * Offset to unset.
     *
     * @param string|int $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (gettype($offset) === 'string') {
            $offset = $this->getOffsetFromName($offset);
        }

        if (gettype($offset) === 'integer') {
            parent::offsetUnset($offset);
        }
    }

    /**
     * Search for the offset that corresponds with the parameter's name.
     *
     * @param string $name
     * @return int|null
     */
    protected function getOffsetFromName(string $name): ?int
    {
        foreach ($this as $key => $parameter) {
            if ($parameter->getName() === $name) {
                return $key;
            }
        }

        return null;
    }
}
