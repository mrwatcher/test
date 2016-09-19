<?php
/**
 * yield test
 * by chens
 */
class xrange implements Iterator
{
	function __construct($start,$end,$step = 1)
	{
		$this->__s = $start;
		$this->__e = $end;
		$this->__step = $step;
	}
    function rewind()
    {
    	$this->v = $this->__s;
    	$this->k = 0;
    }
    function current()
    {
    	return $this->v;
    }
    function key()
    {
    	return $k;
    }

    function next()
    {
    	$this->v = $this->v + $this->__step;
    	$this->k++;
    }

    function valid()
    {
    	if($this->v > $this->__e){
    		return false;
		}else{
			return true;
		}
    }
}
//$e = range(1,100000000000);
$e = new xrange(1,100000000000,10);
foreach($e as $v){
	echo $v.PHP_EOL;
}

function xrange($s,$e,$step=10)
{
	for ($i=$s; $i<=$e; $i +=$step) { 
		yield $i;
	}
}

foreach(xrange(1,100) as $v){
	echo $v.PHP_EOL;
}