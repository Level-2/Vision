<?php 
namespace Vision\Hook;
class ObjectSet implements \Vision\Hook {
	private $objects;

	public function __construct($objects) {
		$this->objects = $objects;
	}
		
	/**
	* Takes an element that matches the tag and replaced it multiple times, one for
	* each entry in $this->objects
	* @param $element - The element to be replaced
	* @return \DomNode - The node that $element will be replaced with
	*/
	public function run(\DomElement $element) {
		$return = [];
	
		$alt = explode(',', $element->getAttribute('alternate'));
		$alttag = $element->getAttribute('alternate-marker') ?: '#alt#';
		$tagName = $element->getAttribute('into') ?: 'item';
		
		$c = 0;
		foreach ($this->objects as $object) {
			$clone = $element->cloneNode(true);
			$newDoc = new \DomDocument;
			$newEl = $newDoc->importNode($clone, true);
			$newEl->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $element->namespaceURI, $element->namespaceURI);		
			$newDoc->appendChild($newEl);
			
			if ($alt) {
				$xpath = new \DomXPath($newDoc);
				foreach ($xpath->query('//*[contains(concat(\' \', @*, \' \'), \'' . $alttag . '\')]') as $el) {
					foreach ($el->attributes as $attr) 	$attr->value = str_replace($alttag, $alt[$c++ % count($alt)], $attr->value);
				}
			}
			
			//Create a new template to use the ObjectHook functionality
			$template = new \Vision\Template($newDoc);
			$template->addHook($tagName . '.*', new Object($object), true);
			$template->output();

			foreach ($newDoc->documentElement->childNodes as $child) $return[] = $element->ownerDocument->importNode($child, true);	
		}
		return $return;
	}
}
