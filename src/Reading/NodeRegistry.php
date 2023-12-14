<?php

namespace SVG\Reading;

use SVG\Nodes\NodeType;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGGenericNodeType;

/**
 * This class enables dynamic instantiation of all known SVG node types.
 */
class NodeRegistry
{
    /**
     * Instantiate a node class matching the given type.
     * If no such class exists, a generic one will be used.
     *
     * @param string $type The node tag name ('svg', 'rect', 'title', etc.).
     *
     * @return SVGNode The node that was created.
     */
    public static function create(string $type): SVGNode
    {
        if (($nodeClass = NodeType::tryFrom($type)) !== null) {
            return new $nodeClass();
        }

        return new SVGGenericNodeType($type);
    }
}
