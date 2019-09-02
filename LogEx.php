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
   

   private static $instance;
   private static $level     = self::L_ERROR;
   private static $logfile   = 'debug.log';
   public $is_pid    = FALSE;
   
   private function __construct() {}
   private function __clone() {}
   private function __wakeup() {}

   public static function getInstance()
   {
      if ( empty(self::$instance) )
      {
         self::$instance = new self();
         if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
         if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
         if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));
      }
      return self::$instance;
   }
   
   public static function setLevel($level)
   {
      if ($level < 0 || $level > 4)
         self::$level = self::L_ERROR;
      else
         self::$level = $level;
   }
   
   public static function setLogFile($logfile)
   {
      // :TODO: Сделать проверку на возможность записи файла
      self::$logfile = $logfile;
   }

   public static function getLogFile()
   {
      return self::$logfile;
   }

   public function clear()
   {
      if (self::$logfile == '-')
         return;
      $fh = fopen(self::$logfile, 'w');
      fclose($fh);
   }
   
   public function log($level, $message)
   {
      if (!isset($message) || !$message || !preg_match('/^\d+$/', $level))
         return FALSE;
      $this->isCorrectLog();
      if ($level < 0 || $level > 4)
      {
         $level = 0;
      }
      if ($level <= self::$level)
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
         
         if (self::$logfile == '-')
         {
            fwrite(STDERR, $message);
         }
         else
         {
            $fh = fopen(self::$logfile, 'a');
            fwrite($fh, $message);
            fclose($fh);
            if ($level == self::L_ERROR)
               fwrite(STDERR, $message);
         }
      }
      if ($level == self::L_ERROR)
         exit(2);
   }
   
   private function isCorrectLog()
   {
      if (self::$logfile == "-")
         return;
      if (!is_writable(self::$logfile))
      {
         self::$logfile = "-";
         $this->log(self::L_WARN, "The log file is NOT writable, changing to STDERR.");
      }
   }
}

?>
