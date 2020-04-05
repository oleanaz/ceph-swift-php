# ceph-swift-php

ceph swift api client for php


## Usage
<pre>
$config = [
    'host' => 'http://127.0.0.1:1234',
    'auth-user' => 'auth-user',
    'auth-key' => 'auth-key',
    'temp-url-key' => 'key'
];
</pre>

<pre>
$client = new \Liushuangxi\Ceph\SwiftClient($config);
</pre>

### Operations with containers
<pre>
$client->container()->listContainers();
$client->container()->listObjects($container, $params = ['format' => 'json']);
$client->container()->createContainer($container, $headers = []);
$client->container()->isExistContainer($container);
$client->container()->deleteContainer($container);
$client->container()->updateACLs($container, $read = '', $write = '');
$client->container()->updateMetas($container, $values);
</pre>

### Operations with objects
<pre>
$client->object()->createObject($container, $file, $object = '');
$client->object()->isExistObject($container, $object);
$client->object()->deleteObject($container, $object);
$client->object()->updateMetas($container, $object, $values);
$client->object()->getMeta($container, $object);
</pre>

### Temporary URL
<pre>
$client->url()->setKey($key, $key2 = '');
$client->url()->tempUrl($uri, $expire = 60);
</pre>