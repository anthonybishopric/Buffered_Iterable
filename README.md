# Automatically chunk up array walks in PHP

### What is this?

When operating on a large iterable resource, it's often desireable to not have to bring the entire contents of that resource into memory at once. For example, database calls that return tens of thousands of objects can cause PHP out of memory errors.

Buffered_Iterable is a utility to help chunk up sections of a large iterable. Here's an example using a Doctrine ORM query as the target:

```php
	<?php
	/*
	* We initialize a Buffered_Iterable with a closure containing a query. The $interval and $offset
	* fields are calculated for you. The 500 as the second parameter to the constructor says 'chunk it in intervals of 500'
	*/
	$every_user = new Buffered_Iterable(function($interval, $offset) use ($entityManager){
		$query = $entityManager->createQuery("select u from User u");
		$query->setMaxResults($interval);
		$query->setFirstResult($offset);
		return $query->getResult();
	}, 500);
	
	/*
	* Iterate over every user in your database, running one query per 500 users.
	*/
	foreach ($every_user as $user){
		$user->sendEmail("Exciting updates to our service!");
	}
````