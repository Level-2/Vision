<?php 
namespace Vision\Hook;
class Condition implements \Vision\Hook {

	public function run(\DomElement $element) {
		$operator = $this->nextSibling($element);
		if (!is_callable([$this, $operator->localName])) throw new Exception('Invalid comparison: ' . $operator->localName);
		
		$result = call_user_func([$this, $operator->localName], $element->nodeValue, $operator->nodeValue);		

		$tags = ['then' => null, 'else' => null];
		$el = $operator;
		while ($el = $this->nextSibling($el)) {
			$tags[$el->localName] = $el;
		}

		$this->remove($operator);
		$this->remove($tags['then']);
		$this->remove($tags['else']);
		
		if ($result) return $this->getChildNodes($tags['then']);
		else return $this->getChildNodes($tags['else']);
	}

	private function remove($element) {
		if ($element instanceof \DomNodeList) foreach ($element as $el) $this->remove($el);
		else if ($element) $element->parentNode->removeChild($element);
	}

	private function getChildNodes(\DomElement $element) {
		if ($element->hasChildNodes()) return $element->childNodes;
		else return null;
	}

	private function nextSibling(\DomElement $element) {
		$ns = $element->namespaceURI;
		do 	$element = $element->nextSibling;
		while($element && !($element instanceof \DomElement));

		if (!$element || $element->namespaceURI != $ns) return null;
		return $element;
	}

	private function equals($a, $b) {
		return $a == $b;
	}

	private function notequals($a, $b) {
		return $a != $b;
	}
}