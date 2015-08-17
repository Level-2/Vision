<?php 
namespace Vision;
class Template {
	private $hooks = [];
	private $document;
	private $xpath;
	private $namespace;

	public function __construct($doc) {
		if ($doc instanceof \DomDocument) $this->document = $doc;
		else {
			$this->document = new \DomDocument;
			$this->document->loadXML($doc);
		}
	
		$this->xpath = new \DomXPath($this->document);

		$ns = $this->xpath->query('namespace::*');
		if (isset($ns[1])) $this->namespace = $ns[1]->localName;		
	}
	
	private function replace(\DomNode $node, $replacement) {
		if (is_array($replacement) || $replacement instanceof \DomNodeList) {
			foreach ($replacement as $replace) $node->parentNode->insertBefore($replace->cloneNode(true), $node);
			$node->parentNode->removeChild($node);
		}
		else $node->parentNode->replaceChild($replacement, $node);
	}	
	
	private function processHook($tag, $hook, $filter = '') {
		$wildcard = strpos($tag, '.*');
		if ($wildcard !== false) $query = '//' . $this->namespace . ':*[substring-before(local-name(), ".") = \'' . substr($tag, 0, $wildcard)  . '\'' . $filter . ']'; 
		else $query = '//' . $this->namespace . ':' . $tag;		
		
		foreach ($this->xpath->query($query) as $element) $this->replace($element, $hook->run($element));
	}
	

	public function addHook($tag, \Vision\Hook $hook) {
		$this->hooks[$tag] = $hook;	
	}

	private function prePost($which) {
		$postProcessing = $this->document->documentElement->getAttribute($which);

		if ($postProcessing) {
			foreach (explode(',', $postProcessing) as $postProcess) {
				list($tagName, $hookName) = explode(':', $postProcess);
				//Is it a Vision standard hook or a user-defined one?
				$name = strpos(trim($hookName), '\\') === 0 ? trim($hookName) : '\\Vision\\Hook\\' . ucfirst(trim($hookName));
				$this->processHook($tagName, new $name);
			}
		}
	}
	
	public function output() {
		//Run preprocess hooks defined in the <template preprocess=""> tag
		$this->prePost('preprocess');

		//Process empty tags e.g. <tpl:foo.bar /> first, these variables might need to be replaced inside tags with child nodes
		foreach ($this->hooks as $tag => $hook) $this->processHook($tag, $hook, ' and not(node())');

		//Now process tags with child nodes, which will have had any variables already replaced
		foreach ($this->hooks as $tag => $hook) $this->processHook($tag, $hook);
		
		//Run postprocess hooks defined in the <template postprocess=""> tag
		$this->prePost('postprocess');

		//Generate the document by taking only the childnodes of the template, ignoring the <template> and </template> tags
		//TODO: Is there a faster way of doing this without string manipulation on the output or this loop through childnodes?
		$output = '';
		foreach ($this->document->documentElement->childNodes as $node) $output .= $this->document->saveXML($node, LIBXML_NOEMPTYTAG);

		//repair empty tags. Browsers break on <script /> and <div /> so can't avoid LIBXML_NOEMPTYTAG but they also break on <base></base> so repair them
		$output = str_replace(['></img>', '></br>', '></meta>', '></base>', '></link>', '></hr>', '></input>'], ' />', $output);
		return trim($output);
	}
}
