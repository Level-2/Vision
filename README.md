Level-2\Vision
--------------

A lightweight (< 500 lines, the entire library has a total Cyclomatic Complexity < 100) template system that relies on DOM manipulation and is easily extensible.

Features:

- 100% valid XML syntax
- If/Else conditions
- Loops through sets of values
- Reads values from objects
- Calls functions on objects
- Unlimited nesting of the above
- Doesn't use heavy-handed regular expressions
- Incredibly lightweight (< 500 lines)
- Easy to extend

Only has a dependency on the PHP DOM extension which is available in most distributions.


Basic Usage
-----------

All templates are valid XML, you must specifiy a namespace that will be used for processing instructions. The `tpl` namespace is encouraged but you can choose anything and this can be different per template in your application.

```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
	<p>Hello World!</p>
</template>');


echo $template->output(); //prints: <p>Hello World!</p>
```

## Hooks

Vision uses a hook system to match tags. Each hook implements the `\Vision\Hook` interface and is use to target a named tag in the template. 


```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
	<tpl:foo />
</template>');
```

To match the `tpl:foo` tag, assign a hook to the name `foo`:

```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
	<tpl:foo />
</template>');
$template->addHook('foo', new Hook);
```

Wildcars are also supported:

```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
	<tpl:foo.bar />
	<tpl:foo.baz />
</template>');
$template->addHook('foo.*', new Hook);
```


## Predefined hooks

Vision includes some common functionality for you.

### \Vision\Hook\Object

This hook allows reading properties from an object:


```php
$user = new \stdclass;
$user->name = 'Tom';
$user->type = 'admin';

				
$template = new \Vision\Template('<template xmlns:tpl="tpl">
	<p>The user's name is <tpl:user.name /></p>
	<p>The user's type is <tpl:user.type /></p>
</template>');

$template->addHook('user.*', new \Vision\Hook\Object($user));

echo $template->output();

```

Which will print:

```php
<p>The user's name is Tom</p>
<p>The user's type is admin</p>
```


Alternatively, you can use an non-empty tag:


```php
$user = new \stdclass;
$user->name = 'Tom';
$user->type = 'admin';

$template = new \Vision\Template('<template xmlns:tpl="tpl">
		<tpl:user>
			<p>Your name is <tpl:item.name /></p>
			<p>Your type is <tpl:item.type /></p>
		</tpl:user>			
</template>');


//Note: This matches the whole tag `user` rather than `user.*`
$template->addHook('user', new \Vision\Hook\Object($user));

echo $template->output();

```

The user object will be referenced as `item` inside the `<tpl:user>` tag and the output will be the same as before:

```php
<p>The user's name is Tom</p>
<p>The user's type is admin</p>
```

You can change the name of the variable that the user is stored in using the `into` attribute. For example, to rename `item` to `this` you can use:

```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
		<tpl:user>
			<p>Your name is <tpl:item.name /></p>
			<p>Your type is <tpl:item.type /></p>
		</tpl:user>			
</template>');
```


Note that this is all defined in the template and you don't need to make any changes to the php code to achieve this.


### Calling functions

Imagine the user's type was accessible via a method: `$suer->getType()` rather than  a property `$user->type`. Vision uses the Uniform Access Principle and doesn't discriminate between public methods and properties:

```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
		<tpl:user>
			<p>Your name is <tpl:item.name /></p>
			<p>Your type is <tpl:item.getType /></p>
		</tpl:user>			
</template>');
```

This will call the `getType()` method and use the return value in the template.


#### Function arguments

Consider the following class:


```php
class User {
	public $firstName;
	public $surname;

	public function getName($which = 'both') {

		if ($which == 'first') return $this->firstName;
		else if ($which == 'last') return $this->surname;
		else return $this->firstName . ' ' . $surname;

	}
}

```

Vision can call this method like so:

```php
$user = new User;
$user->firstName = 'Tom';
$user->surname = 'Butler';

$template = new \Vision\Template('<template xmlns:tpl="tpl">
		<tpl:user>
			<p>Your name is <tpl:item.getName>
				<tpl:arg>first</tpl:arg>
			</tpl:item.getName>
			</p>
		</tpl:user>			
</template>');


//Note: This matches the whole tag `user` rather than `user.*`
$template->addHook('user', new \Vision\Hook\Object($user));

echo $template->output();

```

Arguments can be provided via the `tpl:arg` element. The contents of these will be used as arguments. You can provide as many `tpl:arg` elements as required.

`tpl:arg` elements can also be used to read other variables:

```php
$user = new User;
$user->firstName = 'Tom';
$user->surname = 'Butler';


$filter = new \stdclass;
$filter->nameType = 'first';


$template = new \Vision\Template('<template xmlns:tpl="tpl">
		<tpl:user>
			<p>Your name is <tpl:item.getName>
				<tpl:arg><tpl:filter.nameType /></tpl:arg>
			</tpl:item.getName>
			</p>
		</tpl:user>			
</template>');

$template->addHook('user', new \Vision\Hook\Object($user));
$template->addHook('filter.*', new \Vision\Hook\Object($filter));

echo $template->output();

```

Which will read the value from `tpl:filter.nameType` and use it as the argument for `user.getName`.




## Sets of objects

Vision can also loop through sets of objects using the `ObjectSet` hook. This works in a similar way to the `Object` hook but will repeat the section for each element in the array:


```php

$users = [];

$user1 = new \stdclass;
$user1->name = 'Bob';
$users[] = $user1;

$user2 = new \stdclass;
$user2->name = 'Jo';
$users[] = $user2;

$user3 = new \stdclass;
$user3->name = 'Pete';
$users[] = $user3;

$template = new \Vision\Template('<template xmlns:tpl="tpl">
    <ul>
    <tpl:users>
        <li>
            <tpl:item.name />
        </li>
    </tpl:users>
    </ul>
</template>
');

$template->addHook('users', \Vision\Hook\ObjectSet($users));

echo $template->output();
```

This will output:

```php
<ul>
	<li>Bob</li>
	<li>Jo</li>
	<li>Pete</li>
</ul>

```

`<tpl:item.*` is an object hook, so everthing that's possible with single objects (reading properties, calling functions, providing arguments) is possible with objects provided as part of a set. Similary you can provide `into="name"` which will be used in place of `item` as the element name. So you could change the above template to:


```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
    <ul>
    <tpl:users into="user">
        <li>
            <tpl:user.name />
        </li>
    </tpl:users>
    </ul>
</template>
');

```

And the output will be the same. Changing the element name just helps with clarity in the template itself and is never affected by the external PHP code.

With looped objects it's still possible to call functions:


```php
$template = new \Vision\Template('<template xmlns:tpl="tpl">
    <ul>
    <tpl:users into="user">
        <li>
            <tpl:user.name /> ( <tpl:user.getType /> )
        </li>
    </tpl:users>
    </ul>
</template>
');

```

Which will call `$user->getType()` and use the return value in place of `<tpl:user.getType />`


## Nested sets

Whenever vision comes across an array it will treat it as a set of objects and loop through it.

For example:


```php

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

echo $template->output();

```

Which will print:

```php
<ul>
    
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
    
    </ul>
```

Vision supports an unlimited level of nesting in this way.