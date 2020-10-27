<?php

namespace SVG;

use SVG\Reading\SVGReader;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGReaderTest extends \PHPUnit\Framework\TestCase
{
    private $xml;
    private $xmlNoViewBox;
    private $xmlNoWH;
    private $xmlUnknown;
    private $xmlValue;
    private $xmlEntities;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg width="37" height="42" viewBox="10 20 74 84" ' .
            'xmlns="http://www.w3.org/2000/svg" ' .
            'xmlns:xlink="http://www.w3.org/1999/xlink" ' .
            'xmlns:testns="test-namespace">';
        $this->xml .= '<rect id="testrect" testns:attr="test" xlink:foo="bar" ' .
            'fill="#ABCDEF" style="opacity: .5; stroke: #AABBCC;" />';
        $this->xml .= '<g>';
        $this->xml .= '<circle cx="10" cy="20" r="42" />';
        $this->xml .= '<ellipse cx="50" cy="60" rx="10" ry="20" />';
        $this->xml .= '</g>';
        $this->xml .= '</svg>';

        $this->xmlNoXmlns  = '<svg>';
        $this->xmlNoXmlns .= '<circle cx="10" cy="20" r="42" />';
        $this->xmlNoXmlns .= '</svg>';

        $this->xmlOnlyOtherXmlns  = '<svg xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xmlOnlyOtherXmlns .= '<circle cx="10" cy="20" r="42" xlink:foo="bar" />';
        $this->xmlOnlyOtherXmlns .= '</svg>';

        $this->xmlNoViewBox  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xmlNoViewBox .= '<svg width="37" height="42" ' .
            'xmlns="http://www.w3.org/2000/svg" ' .
            'xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xmlNoViewBox .= '</svg>';

        $this->xmlNoWH  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xmlNoWH .= '<svg viewBox="10 20 74 84" ' .
            'xmlns="http://www.w3.org/2000/svg">';
        $this->xmlNoWH .= '</svg>';

        $this->xmlUnknown  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xmlUnknown .= '<svg xmlns="http://www.w3.org/2000/svg">';
        $this->xmlUnknown .= '<circle cx="10" cy="20" r="42" />';
        $this->xmlUnknown .= '<unknown foo="bar"><baz /></unknown>';
        $this->xmlUnknown .= '<ellipse cx="50" cy="60" rx="10" ry="20" />';
        $this->xmlUnknown .= '</svg>';

        $this->xmlValue  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xmlValue .= '<svg xmlns="http://www.w3.org/2000/svg">';
        $this->xmlValue .= '<text>hello world</text>';
        $this->xmlValue .= '</svg>';

        $this->xmlEntities  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xmlEntities .= '<svg xmlns="http://www.w3.org/2000/svg">';
        $this->xmlEntities .= '<style id="&quot; foo&amp;bar&gt;" ' .
            'style="display: &amp;none">&quot; foo&amp;bar&gt;</style>';
        $this->xmlEntities .= '<text>&quot; foo&amp;bar&gt;</text>';
        $this->xmlEntities .= '</svg>';
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldReturnAnImageOrNull()
    {
        // should return an instance of SVG
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xml);
        $this->assertInstanceOf('\SVG\SVG', $result);

        // should return null when parsing fails
        $result = $svgReader->parseString('<rect />');
        $this->assertNull($result);
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldSetAllAttributesAndNamespaces()
    {
        // should retain all document attributes and namespaces
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xml);
        $this->assertEquals(array(
            'width' => '37',
            'height' => '42',
            'viewBox' => '10 20 74 84',
        ), $result->getDocument()->getSerializableAttributes());
        $this->assertEquals(array(
            '' => 'http://www.w3.org/2000/svg',
            'xlink' => 'http://www.w3.org/1999/xlink',
            'testns' => 'test-namespace',
        ), $result->getDocument()->getSerializableNamespaces());

        // should deal with missing viewBox
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlNoViewBox);
        $this->assertEquals(array(
            'width' => '37',
            'height' => '42',
        ), $result->getDocument()->getSerializableAttributes());

        // should deal with missing width/height
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlNoWH);
        $this->assertEquals(array(
            'viewBox' => '10 20 74 84',
        ), $result->getDocument()->getSerializableAttributes());

        // should set all attributes, including namespace prefixed ones
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xml);
        $rect = $result->getDocument()->getChild(0);
        $this->assertEquals(array(
            'id' => 'testrect',
            'testns:attr' => 'test',
            'xlink:foo' => 'bar',
        ), $rect->getSerializableAttributes());
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldSetStyles()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xml);
        $rect = $result->getDocument()->getChild(0);

        // should detect style attributes
        $this->assertNull($rect->getAttribute('fill'));
        $this->assertSame('#ABCDEF', $rect->getStyle('fill'));

        // should parse and set the 'style' attribute
        $this->assertEquals('.5', $rect->getStyle('opacity'));
        $this->assertEquals('#AABBCC', $rect->getStyle('stroke'));
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldConvertUnitlessCSSLengths()
    {
        $code  = '<svg xmlns="http://www.w3.org/2000/svg">';
        $code .= '<text font-size="11" letter-spacing="2" word-spacing="3">foo</text>';
        $code .= '</svg>';

        $svgReader = new SVGReader();
        $result = $svgReader->parseString($code);

        $text = $result->getDocument()->getChild(0);

        $this->assertSame('11px', $text->getStyle('font-size'));
        $this->assertSame('2px', $text->getStyle('letter-spacing'));
        $this->assertSame('3px', $text->getStyle('word-spacing'));
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldRecursivelyAddChildren()
    {
        // should recursively add all child nodes
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xml);
        $g = $result->getDocument()->getChild(1);

        $this->assertSame(2, $g->countChildren());

        $circle = $g->getChild(0);
        $this->assertEquals(array(
            'cx' => '10',
            'cy' => '20',
            'r' => '42',
        ), $circle->getSerializableAttributes());

        $ellipse = $g->getChild(1);
        $this->assertEquals(array(
            'cx' => '50',
            'cy' => '60',
            'rx' => '10',
            'ry' => '20',
        ), $ellipse->getSerializableAttributes());
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldWorkWithoutAnyXmlns()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlNoXmlns);
        $doc = $result->getDocument();

        $this->assertSame(1, $doc->countChildren());
        $this->assertSame('circle', $doc->getChild(0)->getName());
        $this->assertSame('10', $doc->getChild(0)->getAttribute('cx'));
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldWorkWithoutMainXmlns()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlOnlyOtherXmlns);
        $doc = $result->getDocument();

        $this->assertSame(1, $doc->countChildren());
        $this->assertSame('circle', $doc->getChild(0)->getName());
        $this->assertSame('10', $doc->getChild(0)->getAttribute('cx'));
        $this->assertSame('bar', $doc->getChild(0)->getAttribute('xlink:foo'));
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldRetrieveUnknownNodes()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlUnknown);
        $doc = $result->getDocument();

        // should include unknown nodes
        $this->assertSame(3, $doc->countChildren());
        $this->assertSame('circle', $doc->getChild(0)->getName());
        $this->assertSame('unknown', $doc->getChild(1)->getName());
        $this->assertSame('ellipse', $doc->getChild(2)->getName());

        // should set attributes on unknown nodes
        $this->assertSame('bar', $doc->getChild(1)->getAttribute('foo'));

        // should include children of unknown nodes
        $this->assertSame(1, $doc->getChild(1)->countChildren());
        $this->assertSame('baz', $doc->getChild(1)->getChild(0)->getName());
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldSetValue()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlValue);
        $doc = $result->getDocument();

        // should set value on nodes
        $this->assertSame('hello world', $doc->getChild(0)->getValue());
    }

    /**
     * @covers SVG\Reading\SVGReader
     */
    public function testShouldDecodeEntities()
    {
        $svgReader = new SVGReader();
        $result = $svgReader->parseString($this->xmlEntities);
        $doc = $result->getDocument();

        // should decode entities in attributes
        $this->assertSame('" foo&bar>', $doc->getChild(0)->getAttribute('id'));
        $this->assertSame('&none', $doc->getChild(0)->getStyle('display'));

        // should decode entities in style body
        $this->assertSame('" foo&bar>', $doc->getChild(0)->getCss());

        // should decode entities in value
        $this->assertSame('" foo&bar>', $doc->getChild(1)->getValue());
    }

    // the following requirement is due to SimpleXMLElement::getDocNamespaces
    // not accepting 2 arguments below 5.4
    /**
     * @requires PHP 5.4
     * @covers SVG\Reading\SVGReader
     */
    public function testChildKeepsNamespaces()
    {
        $code  = '<svg xmlns="http://www.w3.org/2000/svg">';
        $code .= '<foreignObject width="500" height="500" x="20" y="20">';
        $code .= '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="bar">';
        $code .= '<p>a</p>';
        $code .= '</div>';
        $code .= '</foreignObject>';
        $code .= '</svg>';

        $svgReader = new SVGReader();
        $result = $svgReader->parseString($code);

        $this->assertContains('<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="bar">', '' . $result);
    }

    /**
     * @requires PHP 5.4
     * @covers SVG\Reading\SVGReader
     */
    public function testParsesChildNamespacedAttributes()
    {
        $code  = '<svg xmlns="http://www.w3.org/2000/svg">';
        $code .= '<foreignObject width="500" height="500" x="20" y="20">';
        $code .= '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="bar">';
        $code .= '<p foo:xxx="yyy">a</p>';
        $code .= '</div>';
        $code .= '</foreignObject>';
        $code .= '</svg>';

        $svgReader = new SVGReader();
        $result = $svgReader->parseString($code);

        $this->assertContains('<p foo:xxx="yyy">', '' . $result);
    }
}
