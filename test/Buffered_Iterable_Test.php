<?php

require_once 'Buffered_Iterable.php';

class Buffered_Iterable_Test extends PHPUnit_Framework_TestCase
{
	
	public function test_buffered_iterable_should_execute_receiver_4_times_if_there_are_12_elements_at_a_interval_rate_of_3()
	{
		$offsets = array();
		$buffered = new Buffered_Iterable(function($interval, $offset) use (&$offsets)
		{
			if( count($offsets)>= 4)
			{
				return array();
			}
			$offsets[] = $offset;
			return array(1,2,3);
		}, 3, 0);

		$total = 0;
		foreach ($buffered as $value)
		{
			$total += $value;
		}
		$this->assertEquals(24, $total, "summing the array (1,2,3) 4 times should yield 6x4=24");
		
		$this->assertEquals(4, count($offsets), "expected the offsets to be 4 long");
		$this->assertEquals(0, $offsets[0]);
		$this->assertEquals(3, $offsets[1]);
		$this->assertEquals(6, $offsets[2]);
		$this->assertEquals(9, $offsets[3]);
	}

	public function test_buffered_iterable_should_find_subsets_of_2_and_then_a_last_subset_of_1_with_an_oddly_numbered_array_at_interval_2()
	{
		$target = array('afoo','bfoo','cfoo','dfoo','efoo');
		$hit_count = 0; // 2 times for a full subset, 1 time for the partial, 1 last time for the end check
		$buffered = new Buffered_Iterable(function($interval, $offset) use ($target, &$hit_count)
		{
			$hit_count++;
			return array_slice($target, $offset, $interval);
		}, 2, 0);
		$result = array();
		foreach($buffered as $foo)
		{
			$result[] = $foo;
		}
		$this->assertEquals(4, $hit_count, "expected the hit count to be 4");
		$this->assertEquals($target, $result, "expected the resulting array to be equal");
		
	}
	
}