--TEST--
Re-entering Lua during a callback to PHP
--FILE--
<?php
$sandbox = new LuaSandbox;
$chunk = $sandbox->loadString('
	function factorial(n)
		if n <= 1 then
			return 1
		else
			return n * test.factorial(n - 1)
		end
	end

	return factorial
');

$ret = $chunk->call();
$luaFactorial = $ret[0];

$sandbox->registerLibrary( 'test', array( 'factorial' => 'factorial' ) );

function factorial($n) {
	global $luaFactorial;
	if ($n <= 1) {
		return array(1);
	} else {
		$ret = $luaFactorial->call($n - 1);
		return array($n * $ret[0]);
	}
}

print implode('', factorial(10)) . "\n";
var_dump( $luaFactorial->call(10) );

try {
	$luaFactorial->call(1000000000);
} catch ( LuaSandboxError $e ) {
	print $e->getMessage() . "\n";
}
try {
	factorial(1000000000);
} catch ( LuaSandboxError $e ) {
	print $e->getMessage() . "\n";
}
--EXPECT--
3628800
array(1) {
  [0]=>
  int(3628800)
}
C stack overflow
C stack overflow
