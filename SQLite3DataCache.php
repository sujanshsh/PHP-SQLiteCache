<?php

class SQLite3DataCache
{
    private $sqlite3_db; //SQLite3 db

    public function __construct() {
        $target_directory = __DIR__.'/../../cache_files';
        $file_name = 'cache.sqlite3db';
        if (!file_exists($target_directory) && !mkdir($target_directory, 0777, true)) {
            die('Failed to create folder/file');
        }
        $this->sqlite3_db = new SQLite3($target_directory.'/'.$file_name,SQLITE3_OPEN_CREATE|SQLITE3_OPEN_READWRITE);
        $stmt = @$this->sqlite3_db->prepare("SELECT * FROM cache");
        if($stmt===false) {
            $this->sqlite3_db->exec("CREATE TABLE cache
                (server_name VARCHAR(35),
                key VARCHAR(40),
                value VARCHAR(100),
                valid_upto_unixtime INTEGER)");
        }
    }
    
    public function get($key) {
        $cur_unixtime = time();
        $output = [];
        $output['value'] = false;
        $output['available'] = false;
        $key = $this->sqlite3_db->escapeString($key);
        // fetch only first column, only single row
        $result = $this->sqlite3_db->querySingle("
            SELECT value from cache where key='$key' and 
            (valid_upto_unixtime=0 or valid_upto_unixtime>=$cur_unixtime)");
        if($result===false) {
            $output['message'] = 'cache query error';
            return $output;
        }
        if($result===null) { // empty array is returned if all columns fetched
            return $output;
        }
        $output['available'] = true;
        $output['value'] = $result;
        return $output;
    }
    
    public function set($key,$value,$valid_upto = 0) {
        $key = $this->sqlite3_db->escapeString($key);
        $value = $this->sqlite3_db->escapeString($value);

        $result = $this->sqlite3_db->exec("
            DELETE FROM cache WHERE server_name = '{$_SERVER['SERVER_NAME']}'
            and key='$key'");
        
        if($result===false)
            return false;
        
        $result = $this->sqlite3_db->exec("
            INSERT INTO cache VALUES ('{$_SERVER['SERVER_NAME']}',
        '$key','$value',$valid_upto )");
        return $result;
    }
    
    public function delete($key) {
        $key = $this->sqlite3_db->escapeString($key);
        $value = $this->sqlite3_db->escapeString($value);
        $result = $this->sqlite3_db->exec("
            DELETE FROM cache WHERE key = '$key' ");
        return $result;
    }
    
    public function __destruct() {
        $this->sqlite3_db->close();
    }

}

