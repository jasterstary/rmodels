<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;
use \RedBeanPHP\Logger as Logger;

class RLogger implements Logger {

  /**
   * @var string $file
   */
  private $file;

  /**
   * @var array<mixed> $_log
   */
  private static array $_log = [];

  /**
   * @var int $_cfg
   */
  private static int $_cfg = 0;
  /**
  * constructor
  *
  * @param string $file
  */
  public function __construct(string $file) {
    $this->file = $file;
    self::$_cfg = 1|2|4|8;
  }

  /**
  * log
  *
  * @return void
  */
    public function log(): void {
      $query = trim(func_get_arg(0));
      if  (
        (self::$_cfg&1)
        &&(preg_match( '/^(CREATE|ALTER)/', $query ))
      ) {
        file_put_contents( $this->file, "{$query};\n",  FILE_APPEND );
      } else if (
        (self::$_cfg&2)
        &&(preg_match( '/^(INSERT|SELECT|UPDATE|DELETE)/', $query ))
      ) {
        self::$_log[] = [
          'query' => $query,
          'params' => func_get_arg(1)
        ];
      } else if (
        (self::$_cfg&4)
        && (preg_match( '/^resultset\: (\d+) rows/', $query, $matches ))
        && (count(self::$_log))
        ) {
            self::$_log[count(self::$_log)-1]['count'] = $matches[1];
      } else if (
          (self::$_cfg&8)
      ) {
            self::$_log[] = func_get_args();
      }
    }

  /**
  * dump
  *
  * @return array<mixed>
  */
    public function dump():array {
      return self::$_log;
    }
}
