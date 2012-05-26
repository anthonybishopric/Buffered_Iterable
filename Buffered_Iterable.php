<?php

class Buffered_Iterable implements IteratorAggregate
{
	private $interval;
	private $offset;
	private $receiver;
	
	public function __construct($receiver, $interval=1, $offset=0)
	{
		$this->interval = $interval;
		$this->offset = $offset;
		if (is_a($receiver, 'Closure'))
		{
			$this->receiver = new Closure_Receiver($receiver);
		}
		else
		{
			$this->receiver = $receiver;
		}
	}
	
	public function getIterator()
	{
		return new Buffered_Iterator($this->receiver, $this->interval, $this->offset);
	}
}

class Buffered_Iterator implements Iterator
{
	private $interval;
	private $offset;
	private $receiver;
	
	private $active = true;
	
	private $iterator_keys = null;
	
	private $current_key = null;
	private $current_value = null;
	
	private $cached_iterator;
	
	public function __construct(Buffered_Receiver $receiver, $interval, $offset)
	{
		$this->receiver = $receiver;
		$this->interval = $interval;
		$this->offset = $offset;
		$this->current_offset = $offset;
	}
	
	public function rewind()
	{	
		$this->current_offset = $this->offset;
		$this->cached_iterator = null;
		$this->iterator_keys = null;
		$this->next();
	}
	
	public function next()
	{
		$receiver = $this->receiver;
		
		if ($this->cached_iterator === null || empty($this->iterator_keys))
		{
			// cached iterator is only strictly null on the first run.
			
			$this->cached_iterator = $receiver->get_subset($this->interval, $this->current_offset);
			$this->current_offset+= $this->interval;
			
			if (empty($this->cached_iterator))
			{
				$this->active = false;
				$this->current_key = null;
				$this->current_value = null;
				// it's over!
				return;
			}

			$this->iterator_keys = array_keys($this->cached_iterator);
		}
		
		$this->current_key = array_shift($this->iterator_keys);
		$this->current_value = $this->cached_iterator[$this->current_key];
	}
	
	public function current()
	{
		return $this->current_value;
	}
	
	public function key()
	{
		return $this->current_key;
	}
	
	public function valid()
	{
		return $this->active;
	}
}

interface Buffered_Receiver
{
	public function get_subset($interval, $offset);
}

class Closure_Receiver implements Buffered_Receiver
{
	private $receiver;
	
	public function __construct($receiver)
	{
		$this->receiver = $receiver;
	}
	
	public function get_subset($interval, $offset)
	{
		$r = $this->receiver;
		return $r($interval, $offset);
	}
}