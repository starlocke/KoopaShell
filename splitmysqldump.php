#!/usr/bin/php
<?php
class SplitMysqlDump{
  var $fh; // file handle
  var $fp; // file pointer for the start of a line.
  const STRUCT_TABLE = "-- Table structure";
  const STRUCT_DATA = "-- Dumping data";
  const PEEK_LEN = 24;
  const ACTION_TABLE = "TABLE";
  const ACTION_DATA = "DATA";
  function cli(&$argv){
    echo "===splitmysqldump.php===\n";
    if(empty($argv[1])){
      echo <<<EOT
Usage:
  splitmysqldump.php [FILE]

EOT;
      exit(__LINE__);
    }
    $this->open($argv[1]);
    $this->dump();
  }

  function open($file){
    if(!is_file($file) || is_dir($file)){
      echo <<<EOT
    File not found: {$file}

EOT;
      exit(__LINE__);
    }

    $this->fh = fopen($file, 'r');
    if(!$this->fh){
      echo <<<EOT
    File could not be read: {$argv[1]}

EOT;
      exit(__LINE__);
    }
  }

  function dump(){
    while($action = $this->seek_action()){
      echo $action."\n";
      $comment = fgets($this->fh);
      echo "-> {$comment}\n";
      preg_match("/`(.*)`/", $comment, $matches);
      list($sql_table, $table) = $matches; // extract table name from array for convenience.
      // Start extraction
      $out = fopen("{$table}.{$action}.sql", "w+");
      // Write the comment-meta-header
      fwrite($out, "--\n");
      fwrite($out, $comment); // the extracted comment
      $comment = fgets($this->fh);
      fwrite($out, $comment); // the next line is expected to be a plain "--"
      while(false !== ($block = fgets($this->fh, 65536))){
        $peek = substr($block, 0, 2);
        //echo "$block~~~~~~~~~~~~~\n";
        //echo "$peek ++++++\n";
        if($peek == "--"){
          break;
        }
        fwrite($out, $block);
      }
      fclose($out);
    }
  }


  function seek_action(){
    $this->fh;
    // Save file pointer
    $this->fp = ftell($this->fh);
    // Peek 24 bytes
    while($peek = fgets($this->fh, self::PEEK_LEN)){
      $peek_len = strlen($peek);
      $last_char = $peek[$peek_len - 1];
      if(substr($peek, 0, strlen(self::STRUCT_TABLE)) == self::STRUCT_TABLE){
        fseek($this->fh, -($peek_len), SEEK_CUR);
        return self::ACTION_TABLE;
      }
      if(substr($peek, 0, strlen(self::STRUCT_DATA)) == self::STRUCT_DATA){
        fseek($this->fh, -($peek_len), SEEK_CUR);
        return self::ACTION_DATA;
      }
      if($last_char != "\n"){
        while($last_char != "\n" && $scan = fgets($this->fh, self::PEEK_LEN)){
          $scan_len = strlen($scan);
          $last_char = $scan[$scan_len - 1];
        }
      }
    }
    return false;
  }
}

$split = new SplitMysqlDump;
$split->cli($argv);
?>

