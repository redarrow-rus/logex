<?php

class LogEx
{
   const L_ALL    = 4;
   const L_DEBUG  = 3;
   const L_INFO   = 2;
   const L_WARN   = 1;
   const L_ERROR  = 0;
   
   const LEVEL = array(
      'ALL'		   => 4,
      'DEBUG'		=> 3,
      'INFO'		=> 2,
      'WARNING'	=> 1,
      'ERROR'		=> 0
   );
   

   public $level     = self::L_ERROR;
   public $logfile   = 'debug.log';
   public $is_pid    = FALSE;
   
   public function __construct($level = self::L_ERROR, $logfile = "debug.log")
   {
      if ($level < 0 || $level > 4)
         $level = LogEx::L_ERROR;
      $this->level    = $level;
      $this->logfile  = $logfile;
      if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
      if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
      if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

   }
   
   public function clear()
   {
      if ($this->logfile == '-')
         return;
      $fh = fopen($this->logfile, 'w');
      fclose($fh);
   }
   
   public function log($level, $message)
   {
      if (!isset($message) || !$message || !preg_match('/^\d+$/', $level))
         return FALSE;
      if ($level < 0 || $level > 4)
      {
         $level = 0;
      }
      if ($level <= $this->level)
      {
         switch ($level)
         {
            case self::L_ERROR:
               $prefix = "ERROR";
               break;
            case self::L_WARN:
               $prefix = "WARNING";
               break;
            case self::L_INFO:
               $prefix = "INFO";
               break;
            case self::L_DEBUG:
               $prefix = "DEBUG";
               break;
            case self::L_ALL:
               $prefix = "ALL";
               break;
            default:
               $prefix = "UNKNOWN";
               break;
         }
         $stamp = date("[d-m-Y H:i:s]");
         preg_replace('/[\t\s\n]+/', ' ', $message);
         $message = "$stamp" . ($this->is_pid == 0 ? "" : " [" . getmypid() . "]" ) . " $prefix: $message\n";
         
         if ($this->logfile == '-')
         {
            fwrite(STDERR, $message);
         }
         else
         {
            $fh = fopen($this->logfile, 'a');
            fwrite($fh, $message);
            fclose($fh);
            if ($level == self::L_ERROR)
               fwrite(STDERR, $message);
         }
      }
      if ($level == self::L_ERROR)
         exit(2);
   }
}

?>