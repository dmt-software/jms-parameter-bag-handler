# ParameterBag Handler

[![Build Status](https://travis-ci.org/dmt-software/jms-parameter-bag-handler.svg?branch=master)](https://travis-ci.org/dmt-software/jms-parameter-bag-handler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dmt-software/jms-parameter-bag-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dmt-software/jms-parameter-bag-handler/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/dmt-software/jms-parameter-bag-handler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dmt-software/jms-parameter-bag-handler/?branch=master)

## Install
`composer require dmt-software/jms-parameter-bag-handler`

## Usage
### Configure Serializer

```php
<?php
 
use DMT\Serializer\ParameterBagHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
 
$serializer = SerializerBuilder::create()
    ->configureHandlers(
        function (HandlerRegistry $registry) {
            $registry->registerSubscribingHandler(new ParameterBagHandler());
        }
    )
    ->build();
```

### Enable ParameterBag

The serializer behavior is extended with a new type called ParameterBag.
To use this functionality add the JMS Serializer annotation ```@Type("ParameterBag")``` to the property of your class.
 
By default the ParameterBag uses instances of ```DMT\Serializer\Parameter```. You can override this behavior by adding 
the FQCN to the ParameterBag type, eg ```@Type("ParameterBag<MyNamespace\MyParameter>")```. If you do so, make sure your
custom parameters implement the ```DMT\Serializer\ParameterInterface```.

```php
<?php
 
use DMT\Serializer\ParameterBag;
use JMS\Serializer\Annotation as JMS;
 
class Entity
{
    /**
     * @var ParameterBag
     *
     * @JMS\Type("ParameterBag")
     */
    protected $parameters;
 
    /**
     * @return ParameterBag
     */
    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }
 
    /**
     * @param ParameterBag $parameters
     */
    public function setParameters(ParameterBag $parameters): void
    {
        $this->parameters = $parameters;
    }
}
```

Further reading on [JMS-serializer](https://jmsyst.com/libs/serializer), like YAML or XML configuration, visit 
https://jmsyst.com/libs/serializer

### Deserialize Json

```php
<?php
 
use DMT\Serializer\ParameterInterface;
use JMS\Serializer\Serializer;
 
/** @var Serializer $serializer */ 
$object = $serializer->deserialize('{"parameters":{"foo":"bar", "baz":false}}', Entity::class, 'json');
$parameters = $object->getParameters();

// iterate over all the parameters 
foreach ($parameters as $parameter) {
    /** @var ParameterInterface $parameter */
    var_dump($parameter->getValue()); // outputs string(3) "bar", bool(false) 
}
 
// or access an expected parameter by it's name
var_dump($parameters['baz']); // outputs class DMT\Serializer\Parameter#61 (2) { ... }
```

### Serialize to Json

```php
<?php 
 
use DMT\Serializer\Parameter;
use DMT\Serializer\ParameterBag;
use JMS\Serializer\Serializer;

$parameter = new Parameter();
$parameter->setName('foo');
$parameter->setValue(1);
 
$object = new Entity();
$object->setParameters(new ParameterBag([$parameter]));
 
/** @var Serializer $serializer */ 
echo $serializer->serialize($object, 'json'); // outputs {"parameters":{"foo":1}}
```