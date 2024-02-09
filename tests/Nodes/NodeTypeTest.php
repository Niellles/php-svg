<?php

namespace SVG\Nodes;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use SVG\Nodes\Shapes\SVGRect;
use ValueError;

class NodeTypeTest extends TestCase
{
    /** @dataProvider provideCases */
    public function testCases($nodeTypeFQN)
    {
        $SVGNodeFqn = SVGNode::class;
        $this->assertTrue(is_a($nodeTypeFQN, $SVGNodeFqn, true), "$nodeTypeFQN is not a $SVGNodeFqn");
    }

    /** @provides testCases */
    public function provideCases()
    {
        foreach (NodeType::cases() as $nodeType => $fqn) {
            yield $nodeType => [$fqn];
        }
    }

    /** @dataProvider  provideEnforceNodeTypeNamingConvention */
    public function testEnforceNodeTypeNamingConvention($nodeType, $nodeFQN)
    {
        $this->markTestSkipped("Should be completed after confirmation this is desirable");
        [$result, $message] = $this->checkNamingConvention($nodeType, $nodeFQN);
        $this->assertTrue($result, $message);
    }

    public function provideEnforceNodeTypeNamingConvention()
    {
        foreach (NodeType::cases() as $nodeType => $fqn) {
            yield $nodeType => [$nodeType, $fqn];
        }
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

    private function checkNamingConvention(string $nodeType, string $nodeFQN): array
    {
        $expectedNodeTypeName = $this->nodeFqnToNodeType($nodeFQN);
        $result = ($expectedNodeTypeName == $nodeType);

        if ($result === false) {
            $message = sprintf(
                'Expected node type "%s" got %s',
                $expectedNodeTypeName,
                $nodeType
            );
        }

        return [$result, $message ?? ''];
    }

    private function nodeFqnToNodeType($nodeFQN): string
    {
        // In: \Nodes\Embedded\SVGForeignObject
        // out: feMergeNode
        $fqnParts = explode('\\', $nodeFQN);
        $className = end($fqnParts);
        $expectedNodeType = Str::replaceStart('SVG', '', $className);

        if (Str::startsWith($expectedNodeType, 'FE')) {
            $expectedNodeType = Str::replaceStart('FE', 'fe', $expectedNodeType);
        } else {
            $expectedNodeType = lcfirst($expectedNodeType);
        }

        return $expectedNodeType;
    }
}
