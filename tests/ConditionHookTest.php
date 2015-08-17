<?php
class ConditionHookTest extends PHPUnit_Framework_TestCase {

	private function getDoc($xml) {
		$doc = new \DomDocument();
		$doc->loadXml('<template xmlns:tpl="tpl">' . $xml  . '</template>');

		return $doc;
	}

	public function testConditionEquals() {
		$doc = $this->getDoc('
			<tpl:if>1</tpl:if>
			<tpl:equals>1</tpl:equals>
			<tpl:then>matched</tpl:then>');


		$hook = new \Vision\Hook\Condition();


		$element = $doc->getElementsByTagName('if')[0];
		$result = $hook->run($element);

		$this->assertEquals($result->item(0)->nodeValue, 'matched');
	}

	public function testConditionEqualsElse() {
		$doc = $this->getDoc('
			<tpl:if>1</tpl:if>
			<tpl:equals>2</tpl:equals>
			<tpl:then>matched</tpl:then>
			<tpl:else>notmatched</tpl:else>');


		$hook = new \Vision\Hook\Condition();


		$element = $doc->getElementsByTagName('if')[0];
		$result = $hook->run($element);

		$this->assertEquals($result->item(0)->nodeValue, 'notmatched');
	}

	public function testConditionNotEquals() {
		$doc = $this->getDoc('
			<tpl:if>1</tpl:if>
			<tpl:notequals>2</tpl:notequals>
			<tpl:then>matched</tpl:then>');


		$hook = new \Vision\Hook\Condition();


		$element = $doc->getElementsByTagName('if')[0];
		$result = $hook->run($element);

		$this->assertEquals($result->item(0)->nodeValue, 'matched');
	}

	/** an integration test. Does using it with a template perform as expected? */
	public function testConditionIntegration() {
		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:if>1</tpl:if>
			<tpl:equals>1</tpl:equals>
			<tpl:then>matched</tpl:then>
			<tpl:else>notmatched</tpl:else>
		</template>');


		$hook = new \Vision\Hook\Condition();
		$template->addHook('if', new \Vision\Hook\Condition);

		$this->assertEquals('matched', $template->output());
	}


	/** an integration test. Does using it with a template perform as expected? */
	public function testConditionIntegrationElse() {
		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:if>1</tpl:if>
			<tpl:equals>2</tpl:equals>
			<tpl:then>matched</tpl:then>
			<tpl:else>notmatched</tpl:else>
		</template>');


		$hook = new \Vision\Hook\Condition();
		$template->addHook('if', new \Vision\Hook\Condition);

		$this->assertEquals('notmatched', $template->output());
	}
}
