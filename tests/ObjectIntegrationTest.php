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


	public function testNestedLoop() {
$users = [];

$user1 = new \stdclass;
$user1->name = 'Bob';
$user1->orders = [];

$order = new \stdclass;
$order->id = 123;
$order->total = '101.11';
$user1->orders[] = $order;

$order = new \stdclass;
$order->id = 1034;
$order->total = '25.99';
$user1->orders[] = $order;

$users[] = $user1;


$user2 = new \stdclass;
$user2->name = 'Jo';
$user2->orders = [];

$order = new \stdclass;
$order->id = 984;
$order->total = '123.33';
$user2->orders[] = $order;



$users[] = $user2;


$template = new \Vision\Template('<template xmlns:tpl="tpl">
    <ul>
    <tpl:users into="user">
        <li>
            <tpl:user.name /> has the following orders:

            <table>
            	<thead>
            		<tr>	
            			<th>ID</th>
            			<th>Total</th>
            		</tr>
            	</thead>
            	<tpl:user.orders into="order">
            	<tr>
            		<td><tpl:order.id /></td>
            		<td><tpl:order.total /></td>
            	</tr>
            	</tpl:user.orders>
            </table>
        </li>
    </tpl:users>
    </ul>
</template>
');

$template->addHook('users', new \Vision\Hook\ObjectSet($users));

$this->assertEquals('<ul>
    
        <li>
            Bob has the following orders:

            <table>
            	<thead>
            		<tr>	
            			<th>ID</th>
            			<th>Total</th>
            		</tr>
            	</thead>
            	
            	<tr>
            		<td>123</td>
            		<td>101.11</td>
            	</tr>
            	
            	<tr>
            		<td>1034</td>
            		<td>25.99</td>
            	</tr>
            	
            </table>
        </li>
    
        <li>
            Jo has the following orders:

            <table>
            	<thead>
            		<tr>	
            			<th>ID</th>
            			<th>Total</th>
            		</tr>
            	</thead>
            	
            	<tr>
            		<td>984</td>
            		<td>123.33</td>
            	</tr>
            	
            </table>
        </li>
    
    </ul>', $template->output());


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

