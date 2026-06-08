<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;

class RCollection {

  protected array $rows = [];

  protected $repo = null;

  protected $oConditions = null;

  function __construct(array $rows, RRepo $repo, object $oConditions) {
    $this->rows = $rows;
    $this->setRepo($repo);
    $this->oConditions = $oConditions;
  }

  function setRepo($repo) {
    $this->repo = $repo;
    return $this;
  }

  function getRepo() {
    return $this->repo;
  }

  /**
  * toArray
  *
  * @param Callable $func
  * @return <mixed> array
  */
  function toArray(?Callable $func = null):array {
    $re = [];
    foreach ($this->rows as $row) {
      $r = $row->export();
      if (is_callable($func)) {
        $func($r);
      }
      $re[] = $r;
    }
    return $re;
  }

  /**
  * toModels
  *
  * @param Callable $func
  * @return <mixed> array
  */
  function toModels(?Callable $func = null):array {
    $re = [];
    foreach ($this->rows as $row) {
      $dt = $row->box();
      if (method_exists($dt, 'setRepo')) $dt->setRepo($this->repo);
      if (is_callable($func)) {
        $func($dt);
      }
      $re[] = $dt;
    }
    return $re;
  }

  /**
  * toPages
  *
  * @param Callable $func
  * @return <mixed> array
  */
  public function toPages(?Callable $func = null):array {
    R::debug(true);
    ob_start();
    $cnt = R::count($this->oConditions->table, $this->oConditions->countQuery, $this->oConditions->data);
    ob_get_clean();
    $data = [
      'data' => $this->toArray($func),
      //'pagination' => [
        'count' => $cnt,
        'limit' => $this->oConditions->limit,
        'offset' => $this->oConditions->offset,
        //'cond' => $this->oConditions->countQuery
      //]
    ];
    return $data;
  }

    /**
  * getQuery
  *
  * @return <mixed> string
  */
  public function getQuery():string {
    return $this->oConditions->query;
  }

    /**
  * getConditions
  *
  * @return <mixed> string
  */
  public function getConditions():array {
    return (Array)$this->oConditions->response;
  }

}
