<?php
class SampleBasicHook implements Vision\Hook {
	public $match;

	public function run(\DomElement $element) {
		$this->match = $element;
		return $element;
	}
}

class VisionHookTest extends PHPUnit_Framework_TestCase {

	private function getMockHook() {
		return $this->getMockBuilder('Vision\\Hook')->getMock();
	}

	public function testAddHook() {
		$document = new \DomDocument;
		$document->loadXml('<?xml version="1.0"?>
		<template xmlns:tpl="tpl">
			<tpl:foo>test</tpl:foo>
		</template>
		');

		$template = new Vision\Template($document);

		//Tag the hook should match, DomElement Object
		$hook = new SampleBasicHook;
		$template->addHook('foo', $hook);

		$template->output();

		$this->assertEquals('test', $hook->match->nodeValue);
	}

	public function testHookReplace() {
		$template = new Vision\Template('<?xml version="1.0"?>
		<template xmlns:tpl="tpl">
			<tpl:foo>test</tpl:foo><tpl:bar>test 2</tpl:bar>
		</template>
		');

		$mock = $this->getMockHook();
		$mock->expects($this->once())->method('run')->willReturn(new DomText('replaced'));

		$template->addHook('foo', $mock);

		$output = $template->output();
		//Only one of the tags should have been replaced!
		$this->assertEquals($output, 'replaced<tpl:bar>test 2</tpl:bar>');
	}

	public function testWildcardHook() {
		$template = new Vision\Template('<?xml version="1.0"?>
		<template xmlns:tpl="tpl">
			<tpl:foo.bar>test 1</tpl:foo.bar><tpl:foo.baz>test 2</tpl:foo.baz>
		</template>
		');

		$mock = $this->getMockHook();
		//Create a new element each time, using expects(2) returns the same instance
		//which then gets moved around the DOM rather than acting as a replacement
		$mock->expects($this->at(0))->method('run')->willReturn(new DomText('replaced'));
		$mock->expects($this->at(1))->method('run')->willReturn(new DomText('replaced'));

		//With a wildcard hook, both these tags should be replaced:
		$template->addHook('foo.*', $mock);
		$output = $template->output();

		$this->assertEquals('replacedreplaced', $output);
	}


	public function testPostProcessHook() {
		$template = new Vision\Template('<?xml version="1.0"?>
		<template xmlns:tpl="tpl" postprocess="mock.*:\MockHook">
			<tpl:mock.foo />
		</template>
		');


		$output = $template->output();

		$this->assertEquals($output, 'replaced');
	}
	

	public function testPreProcessHook() {
		$template = new Vision\Template('<?xml version="1.0"?>
		<template xmlns:tpl="tpl" preprocess="mock.*:\MockHook">
			<tpl:mock.foo />
		</template>
		');


		$output = $template->output();
		$this->assertEquals($output, 'replaced');
	}	

	public function testComplex() {

		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:foo.getUser>
			<tpl:item.name />
			<tpl:item.getType>
				<tpl:arg><tpl:foo.foo /></tpl:arg>
			</tpl:item.getType>
			</tpl:foo.getUser>
		</template>');


		$hook = new \Vision\Hook\Object(new Model);
		$template->addHook('foo.*', $hook);
		$o = $template->output();
	}
}


class MockHook implements \Vision\Hook {
	public function run(\DomElement $element) {
		return new \DomText('replaced');
	}
}


class Model {
	public $foo = 'bar';

	public function getUser() {
		return new User;
	}
}

class User {
	public $name = 'test';

	public function getType($arg) {
		return 'type';
	}
}


