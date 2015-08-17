<?php

class ObjectHookTest extends PHPUnit_Framework_TestCase {

	private function getDomElement($xml) {
		$document = new \DomDocument;
		$document->loadXml('<doc xmlns:tpl="tpl">' . $xml . '</doc>');

		$element = $document->documentElement->childNodes->item(0);
		return $element;
	}

	public function testSimpleReplacement() {
		$object = new \stdclass;
		$object->foo = 'bar';

		//Can't mock the DomElement without implemening a lot of its functionality, use a real one for the test
		$element = $this->getDomElement('<obj.foo />');

		$hook = new \Vision\Hook\Object($object);

		$result = $hook->run($element);

		$this->assertEquals('bar', $result->nodeValue);
	}

	public function testDeepReplacement() {
		$object = new \stdclass;
		$object->foo = new \stdclass;
		$object->foo->bar = 'baz';

		//Can't mock the DomElement without implemening a lot of its functionality, use a real one for the test
		$element = $this->getDomElement('<obj.foo.bar />');

		$hook = new \Vision\Hook\Object($object);

		$result = $hook->run($element);
		$this->assertEquals('baz', $result->nodeValue);
	}


	public function testInvalidProperty() {
		$object = new \stdclass;
		$object->foo = 'bar';


		//Trying to read a property from the object which doesn't exist should create an exception
		$this->setExpectedException('Exception');

		$element = $this->getDomElement('<obj.nonexistantproperty />');

		$hook = new \Vision\Hook\Object($object);
		$result = $hook->run($element);
	}

	public function testCallMethod() {
		$mock = $this->getMockBuilder('foo')->setMethods(['bar'])->getMock();
		$mock->expects($this->once())->method('bar')->will($this->returnValue([]));

		$element = $this->getDomElement('<tpl:foo.bar />');

		$hook = new \Vision\Hook\Object($mock);
		$result = $hook->run($element);
	}

	public function testCallMethodArgs() {
		$element = $this->getDomElement('<tpl:foo.bar>
			<tpl:arg>X</tpl:arg>
			<tpl:arg>Y</tpl:arg>
			</tpl:foo.bar>');

		$mock = $this->getMockBuilder('foo')->setMethods(['bar'])->getMock();
		$mock->expects($this->once())->method('bar')->with($this->equalTo('X'), $this->equalTo('Y'))->will($this->returnValue([]));


		$hook = new \Vision\Hook\Object($mock);
		$result = $hook->run($element);
	}


	public function testCallMethodInvalidArgs() {
		$element = $this->getDomElement('<tpl:foo.bar>
			<p>A real element</p>
			<tpl:arg>X</tpl:arg>
			<tpl:arg>Y</tpl:arg>
			</tpl:foo.bar>');

		$mock = $this->getMockBuilder('foo')->setMethods(['bar'])->getMock();
		$mock->expects($this->once())->method('bar')->with()->will($this->returnValue([]));

		
		$hook = new \Vision\Hook\Object($mock);
		$result = $hook->run($element);
	}
}
