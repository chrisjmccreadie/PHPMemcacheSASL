## PHPMemcacheSASL for AppFog

This is a modification of [ronnywang](https://github.com/ronnywang/PHPMemcacheSASL)'s implementation of PHPMemcacheSASL specifically for use on [AppFog](http://appfog.com). 

### What's the difference?

On AppFog, the connection information for your Memcachier instance is exposed through three environment variables: 

* ``$_ENV['MEMCACHIER_SERVERS']`` (ex. xxx.ec2.memcachier.com)
* ``$_ENV['MEMCACHIER_USERNAME']`` (ex. aa92mro2in)
* ``$_ENV['MEMCACHIER_PASSWORD']`` (ex. oim23in924b2NS3Iaid1x193jhe1)

The format of $_ENV['MEMCACHIER_SERVERS'] is $HOST:$PORT (ex. xxx.ec2.memcachier:11211). In the [AppFog Memcachier PHP docs](http://docs.appfog.com/add-ons/memcachier#php), the example code suggests that you add a server using the syntax:  

	$m->addServer($_ENV["MEMCACHIER_SERVERS"], '11211');
	// Evaluates as: $m->addServer(xxx.ec2.memcachier.com:11211', '11211');

This is a problem because the first argument of addServer expects the hostname without the port.  

I have modified	the API of $m->addServer to:

	$m->addServer($_ENV["MEMCACHIER_SERVERS"]);
	// Evaluates as: $m->addServer(xxx.ec2.memcachier.com:11211');

This way, you do not need to parse/explode the value of MEMCACHIER_SERVERS before using it in your code. Other implementations (and docs) for PHPMemcacheSASL do not account for this difference in syntax, so they will be unhelpful to you if you are an AppFog client.

### Implementation

First, make sure you have activated the Memcachier add-on in [your AppFog console](https://console.appfog.com/). 

1. Go to the dashboard for your app
2. Click "Add-Ons" in your left navigation
3. Click "Install" under Memcachier
4. Click "Env Variables" in your left navigation. Make sure that your MEMCACHIER_SERVERS, MEMCACHIER_USERNAME, and MEMCACHIER_PASSWORD variables are listed.

Now you are ready to integrate Memcachier with your code. To get started, download PHPMemcacheSASL.php and put it in your working directory. If you have another implementation of PHPMemcacheSASL.php, you must replace it with the file from this project. In this example, I will assume PHPMemcacheSASL.php is in the same directory:

	include('MemcacheSASL.php');
	$m = new MemcacheSASL;
	$m->addServer(getenv('MEMCACHIER_SERVERS'));
	$m->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));

You have now initiated a connection for your Memcachier server. Here is an example communication with the Memcachier server: 

	$m->add('test', '123');
	echo $m->get('test');
	$m->delete('test');

This code will add a cache entry with the key 'test' and a value '123'. It will then immediately retrieve and print the value of the key 'test' (which is '123') from the cache. Finally, it will delete this cache entry.

$m->add() can also accept a third argument, $expiration, which will set the expiration of your value to $expiration seconds from now. To set the same value as above with a one hour expiration, I would write:

	$m->add('test', '123', 3600);
	echo $m->get('test');
	$m->delete('test');

You can see the full code of this example in [example.php](https://github.com/ceslami/PHPMemcacheSASL/blob/master/example.php).

### Used by

If you use this software, and would like to be listed below, just [send me an email](mailto:cyrus@findnewjams.com).

### Revisions, Contact, and Acknowledgements

Please feel free to submit a pull request. I created this project to benefit other users, and plan to continually revise it as per my communications with them. Thanks to [ronnywang](https://github.com/ronnywang/PHPMemcacheSASL) for the base code and [AppFog](http://appfog.com) for providing an awesome hosting solution.

-----

#### Further reading

##### Memcache Binary Protocol  
http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped

##### Memcache SASL Auth Protocol  
http://code.google.com/p/memcached/wiki/SASLAuthProtocol

##### PHP Memcached class  
http://php.net/manual/en/class.memcached.php
