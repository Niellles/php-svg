<?php

namespace SVG\Nodes;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Shapes\SVGRect;
use ValueError;

class NodeTypeTest extends TestCase
{
    public function testCases()
    {
        $this->assertIsArray(NodeType::cases());
    }

    /** @dataProvider provideTryFrom */
    public function testTryFrom($expectedResult, string $type)
    {
        $result = NodeType::tryFrom($type);
        $this->assertSame($expectedResult, $result);
    }

    /** @provides testTryFrom */
    public function provideTryFrom()
    {
        return [
            "It returns correct NodeType FQDN for rect" => [SVGRect::class, 'rect'],
            "It returns 'null' if NodeType is unknown" => [null, 'foobar']
        ];
    }

    public function testFromReturnsNodeFQDN()
    {
        $result = NodeType::from('rect');
        $this->assertSame(SVGRect::class, $result);
    }

    public function testFromThrowsError()
    {
        $this->expectException(ValueError::class);
        NodeType::from('FooBar');
    }
}
