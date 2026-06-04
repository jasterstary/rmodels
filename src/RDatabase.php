<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;
use \JasterStary\RModels\RLogger;

class RDatabase {
    
    function start($conn, $user, $pass) {//getenv('DB_CONN'),getenv('DB_USER'),getenv('DB_PASS')
      R::setup($conn, $user, $pass);
      return $this;
    }
    
    function log($writablePath) {//getenv('DB_WRITABLE')
      $writablePath = realpath($writablePath);
      $this->ml = new RLogger( sprintf( $writablePath.'/migration_%s.sql', date('Y-m-d') ) );
      R::getDatabaseAdapter()
            ->getDatabase()
            ->setLogger($this->ml)
            ->setEnableLogging(TRUE);
      //R::debug( TRUE, 1 ); 
      return $this;
    }
    
    function configure($cfg, $name = 'rdb') {
      if (!isset($cfg[$name])) return $this;
      if (!is_array($cfg[$name])) return $this;
      $cfg = $cfg[$name];
      
      return $this;
    }
    
    function getLog() {
      return $this->ml->dump();  
    }
    
    
}
