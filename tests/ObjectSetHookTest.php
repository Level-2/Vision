<?php
class ObjectSetTest extends PHPUnit_Framework_TestCase {

	private function getDomElement($xml) {
		$document = new \DomDocument;
		$document->loadXml('<doc xmlns:tpl="tpl">' . $xml . '</doc>');

		$element = $document->documentElement->childNodes->item(0);
		return $element;
	}

	private function getFilledArray() {
		$array = [];

		for ($i = 0; $i < 3; $i++) {
			$obj = new \stdclass;
			$obj->id = $i;
			$obj->name = 'Item ' . $i;
			$array[] = $obj;
		}
		return $array;
	}

	//Convert an array of DomNodes to a string, mimicking Template::output
	private function render($xmlList) {
		$output = '';
		foreach ($xmlList as $node) {
			$output .= $node->ownerDocument->saveXml($node);
		}
		return $output;
	}

	public function testBasicLoop() {

		$element = $this->getDomElement('<tpl:loop><tpl:item.id /></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('012', $result);
	}

	public function testMultipleValues() {		
		$element = $this->getDomElement('<tpl:loop><tpl:item.id />::<tpl:item.name /></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('0::Item 01::Item 12::Item 2', $result);
	}

	public function testMultipleValuesWithOtherTags() {		
		$element = $this->getDomElement('<tpl:loop><p><span class="id"><tpl:item.id /></span> <span class="name"><tpl:item.name /></span></p></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('<p><span class="id">0</span> <span class="name">Item 0</span></p><p><span class="id">1</span> <span class="name">Item 1</span></p><p><span class="id">2</span> <span class="name">Item 2</span></p>', $result);
	}	


	public function testChangeItemName() {
		$element = $this->getDomElement('<tpl:loop into="something"><tpl:something.id /></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('012', $result);
	}


	public function testAlternate() {
		$element = $this->getDomElement('<tpl:loop alternate="odd,even"><li class="#alt#"><tpl:item.id /></li></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('<li class="odd">0</li><li class="even">1</li><li class="odd">2</li>', $result);
	}

	public function testAlternateWithOther() {
		$element = $this->getDomElement('<tpl:loop alternate="odd,even"><li class="foo #alt# bar"><tpl:item.id /></li></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('<li class="foo odd bar">0</li><li class="foo even bar">1</li><li class="foo odd bar">2</li>', $result);
	}

	public function testAlternateChangeMarker() {
		$element = $this->getDomElement('<tpl:loop alternate-marker=":::FOO:::" alternate="odd,even"><li class=":::FOO:::"><tpl:item.id /></li></tpl:loop>');

		$objects = $this->getFilledArray();

		$objectSet = new \Vision\Hook\ObjectSet($objects);
		$result = $this->render($objectSet->run($element));

		$this->assertEquals('<li class="odd">0</li><li class="even">1</li><li class="odd">2</li>', $result);
	}
}

