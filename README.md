# PHP-SQLiteCache
Cache small size data using SQLite3 database

Server name is also stored to distinguish localhost and production server

## Example Usage

```php
$dc = new DataCache();

$key = 'max';
$value = 103;

$check = $dc->get($key);
if($check['available']) {
    echo $check['value'];
} else {
    echo 'not available<br>';
    $dc->set($key,$value,time()+5); // valid upto 5 secs from now
    echo $dc->get($key)['value'];
}
```
