<?php
class VisionTest extends PHPUnit_Framework_TestCase {

	public function testBasic() {
		$template = new \Vision\Template('<?xml version="1.0"?>
		<template>
			Test
		</template>
		');

		$this->assertEquals('Test',$template->output());
	}

	public function testEmptyTags() {
			$template = new \Vision\Template('<?xml version="1.0"?>
		<template>
			<script src="foo.js"></script><div></div>
		</template>
		');


		//Script should not be converted to <script />
		//Div should not be converted to <div />
		$this->assertEquals('<script src="foo.js"></script><div></div>', $template->output());
	}

	public function testClosedTags() {
			$template = new \Vision\Template('<?xml version="1.0"?>
		<template>
			<img src="foo.jpg" /><br />
		</template>
		');


		//Img and br should not be expanded to <br></br>
		$this->assertEquals('<img src="foo.jpg" /><br />', $template->output());
	}
}

