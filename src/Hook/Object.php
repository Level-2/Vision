<?php 
namespace Vision\Hook;
class Object implements \Vision\Hook {
	private $object;
	
	public function __construct($object) {
		if (!is_object($object)) throw new \Exception('Invalid object: ' . $object);
		$this->object = $object;
	}

	private function traverse($name, array $args = []) {
		$parts = explode('.', $name);
		array_shift($parts);
		$obj = $this->object;
		foreach ($parts as $ref) {
			if (isset($obj->$ref)) $obj = $obj->$ref;
			else if (is_callable([$obj, $ref])) return call_user_func_array([$obj, $ref], $args);
			else throw new \Exception('Object ' . get_class($obj) . ' does not have property ' . $ref . ':: ' . print_r($obj, true));
		}
		//If the final object is a DateTime, print a date. Why doesn't DateTime implement __toString()?
		return $obj instanceof \DateTime ? $obj->format('Y-m-d H:i:s') : $obj;
	}	
	
	private function getArgs(\DomElement $element) {
		$result = [];

		for ($i = 0; $i < $element->childNodes->length; $i++) {
			$child = $element->childNodes[$i];
			if ($child instanceof \DomElement) {
				if ($child->namespaceURI === $element->namespaceURI && $child->localName === 'arg') {
					$result[] = $child->nodeValue;
					$i--;
					$element->removeChild($child);
				}
				else {
					break; //:arg arguments should be the first elements. Stop if an element is reached that is not an arg
				}
				
			}
		}
		return $result;
	}

	public function run(\DomElement $element) {
		$args = $this->getArgs($element);
		$result = $this->traverse($element->localName, $args);
	
		if (!is_scalar($result)) {
			$clone = $element->cloneNode(true);
			$newDoc = new \DomDocument;
			$root = $newDoc->createElement('template');
			$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $element->namespaceURI, $element->namespaceURI);
			$newDoc->appendChild($root);
						
			$newEl = $newDoc->importNode($clone, true);
			$root->appendChild($newEl);

			$template = new \Vision\Template($newDoc);

			if (is_object($result)) $result = [$result];
			$template->addHook($element->localName, new ObjectSet($result), true);
			$template->output();
			
			$return = [];
			foreach ($newDoc->documentElement->childNodes as $child) $return[] = $element->ownerDocument->importNode($child, true);

			return $return;
		}		
		else return new \DomText($result);
	}
}
