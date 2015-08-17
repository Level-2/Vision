<?php
//Integration test using the entire stack. 
class ObjectIntegrationTest extends PHPUnit_Framework_TestCase {

	public function testBasicObjectData() {
		$user = new \stdclass;
		$user->name = 'Tom';
		$user->type = 'admin';

		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<p>Your name is <tpl:user.name /></p>
		</template>');

		$template->addHook('user.*', new \Vision\Hook\Object($user));

		$this->assertEquals('<p>Your name is Tom</p>', $template->output());
	}


	public function testBasicObjectExpanded() {
		$user = new \stdclass;
		$user->name = 'Tom';
		$user->type = 'admin';

		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:user>
				<p>Your name is <tpl:item.name /></p><p>Your type is <tpl:item.type /></p>
			</tpl:user>			
		</template>');

		$template->addHook('user', new \Vision\Hook\Object($user));

		$this->assertEquals('<p>Your name is Tom</p><p>Your type is admin</p>', $template->output());
	}



	public function testBasicObjectCallMethod() {

/*
//This doesn't seem to work as the method is never called, using a real class (Defined at the end of this file) instead
//Probably something to do with the way is_callable interracts with mocks

		$user = $this->getMockBuilder('User')->getMock();
		$user->name = 'Tom';
		$user->expects($this->once())->method('getType')->with()->willReturn('admin');
*/		
			
		$user = new MockUser;
		$user->name = 'Tom';
		$user->type = 'admin';

		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:user>
				<p>Your name is <tpl:item.name /></p><p>Your type is <tpl:item.getType /></p>
			</tpl:user>			
		</template>');

		$template->addHook('user', new \Vision\Hook\Object($user));

		$this->assertEquals('<p>Your name is Tom</p><p>Your type is admin</p>', $template->output());
	}


		public function testBasicObjectCallMethodArgs() {

		$user = new MockUser;
		$user->name = 'Tom';
		$user->type = 'admin';

		$template = new \Vision\Template('<template xmlns:tpl="tpl">
			<tpl:user>
				<p>Your name is <tpl:item.name /></p><p>Your type is <tpl:item.getArg>
					<tpl:arg>TEST</tpl:arg>
				</tpl:item.getArg></p>
			</tpl:user>			
		</template>');

		$template->addHook('user', new \Vision\Hook\Object($user));

		$this->assertEquals('<p>Your name is Tom</p><p>Your type is TEST</p>', $template->output());
	}
}


class MockUser {
	public $name;
	public $type;

	public function getType() {
		return $this->type;	
	}

	public function getArg($arg) {
		return $arg;
	}
}

