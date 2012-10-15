## PHPMemcacheSASL for AppFog

This is a modification of [ronnywang](https://github.com/ronnywang/PHPMemcacheSASL)'s implementation of PHPMemcacheSASL specifically for use on [AppFog](http://appfog.com). 

### What's the difference?

On AppFog, the connection information for your Memcachier instance is exposed through three environment variables: 

```php
echo $_ENV['MEMCACHIER_SERVERS']  // output: xxx.ec2.memcachier.com:11211
echo $_ENV['MEMCACHIER_USERNAME'] // output: aa92mro2in
echo $_ENV['MEMCACHIER_PASSWORD'] // output: oim23in924b2NS3Iaid1x193jhe1
```

The format of ``$_ENV['MEMCACHIER_SERVERS']`` is ``$HOST:$PORT`` on AppFog's PHP instances. In the [AppFog Memcachier PHP docs](http://docs.appfog.com/add-ons/memcachier#php), the example code suggests that you add a server using the syntax:  

```php
$m->addServer($_ENV["MEMCACHIER_SERVERS"], '11211');
// Evaluates as: $m->addServer(xxx.ec2.memcachier.com:11211', '11211');
```

This is a problem because the first argument of addServer expects the hostname without the port. I have modified the API of $m->addServer to:

```php
$m->addServer($_ENV["MEMCACHIER_SERVERS"]);
// Evaluates as: $m->addServer(xxx.ec2.memcachier.com:11211');
```

This way, you do not need to parse/explode the value of ``$_ENV['MEMCACHIER_SERVERS']`` before using it to connect to Memcachier. Other implementations (and docs) for PHPMemcacheSASL do not account for this difference in syntax, so they will be unhelpful to you if you are an AppFog client.

### Implementation

First, make sure you have activated the Memcachier add-on in [your AppFog console](https://console.appfog.com/). 

1. Go to the dashboard for your app
2. Click "Add-Ons" in your left navigation
3. Click "Install" under Memcachier
4. Click "Env Variables" in your left navigation. Make sure that your ``$_ENV['MEMCACHIER_SERVERS']``, ``$_ENV['MEMCACHIER_USERNAME']``, and ``$_ENV['MEMCACHIER_PASSWORD']`` variables are listed.

Now you are ready to integrate Memcachier with your code. To get started, download MemcacheSASL.php and put it in your working directory. If you have another implementation of MemcacheSASL.php, you must replace it with the file from this project. In this example, I will assume MemcacheSASL.php is in the same directory:

```php
include('MemcacheSASL.php');
$m = new MemcacheSASL;
$m->addServer(getenv('MEMCACHIER_SERVERS'));
$m->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));
```

You have now initiated a connection for your Memcachier server. Here is an example communication with the Memcachier server: 

```php
$m->add('test', '123');
echo $m->get('test'); // output: '123'
$m->delete('test');
```

This code will add a cache entry with the key 'test' and a value '123'. It will then immediately retrieve and print the value of the key 'test' (which is '123') from the cache. Finally, it will delete this cache entry.

$m->add() can also accept a third argument, $expiration, which will set the cache expiration of your key to $expiration seconds from now. To set the same key/value as above with a one hour expiration, I would write:

```php
$m->add('test', '123', 3600); // set expiration of 1hr (60s * 60m)
echo $m->get('test'); // output: '123'
$m->delete('test');
```

You can see the full code of this example in [example.php](https://github.com/ceslami/PHPMemcacheSASL/blob/master/example.php).

Here is a production example using Memcachier to cache results from queries to a MySQL database:

```php
include('MemcacheSASL.php');

///

function memcacheQuery($query, $hours = 1) {
	$m = new MemcacheSASL;
	$m->addServer(getenv('MEMCACHIER_SERVERS'));
	$m->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));
	$key = md5($query);
	
	$songs_array = $m->get($key);
	if( !$songs_array ) {
		$raw_query = mysql_query($query);
		while( $songs = mysql_fetch_array($raw_query) ) {
			$songs_array[] = $songs;
		}
		$cache_entry = $m->add($key, $songs_array, 60*60*$hours);
	}

	return $songs_array;
}
```

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
