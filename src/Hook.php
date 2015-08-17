<?php
namespace Vision;
interface Hook {
	public function run(\DomElement $element);
}