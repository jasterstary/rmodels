<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;

class RModel extends \RedBeanPHP\SimpleModel {

  protected $repo = null;

  function setRepo($repo) {
    $this->repo = $repo;
  }

  function getRepo() {
    return $this->repo;
  }

  function toArray(Callable $func = null) {
    $dd = $this->bean->export();
    if (is_callable($func)) {
      $func($dd);
    }
    return $dd;
  }

  function loadShared(array $names) {
    ob_start();
    foreach ($names as $name) {
      $meth = 'shared' . ucfirst($name) . 'List';
      $this->bean->$meth;
    }
    ob_end_clean();
    return $this;
  }

  function connectShared($o) {
    $c = explode('\\', get_class($o));
    $c = end($c);
    $prop = 'shared' . $c . 'List';
    ob_start();
    $this->bean->$prop[] = $o;
    R::store($this->bean);
    ob_end_clean();
    return $this;
  }

}
