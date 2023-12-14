<?php

namespace SVG\Reading;

use ReflectionProperty;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Nodes\SVGNode;

/**
 * @covers \SVG\Reading\NodeRegistry
 *
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldConstructKnownTypes()
    {
        $result = NodeRegistry::create('rect');
        $this->assertInstanceOf(SVGRect::class, $result);
    }

    /** @dataProvider provideItConstructsAllKnownTypes */
    public function testItConstructsAllKnownTypes($type,$expectedResult)
    {
        $result = NodeRegistry::create($type);
        $this->assertInstanceOf($expectedResult, $result);
        $this->assertInstanceOf(SVGNode::class, $result);
    }

    public function provideItConstructsAllKnownTypes()
    {
        $reflectedProperty = new ReflectionProperty(NodeRegistry::class, 'nodeTypes');
        $reflectedProperty->setAccessible(true);
        $types = $reflectedProperty->getValue();

        foreach ($types as $type => $class) {
            yield "\$type='$type'" => [$type, $class];
        }
    }

    public function testShouldUseGenericTypeForOthers()
    {
        $result = NodeRegistry::create('div');
        $this->assertInstanceOf(SVGGenericNodeType::class, $result);
    }
}
