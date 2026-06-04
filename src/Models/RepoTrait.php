<?php

namespace JasterStary\Rmodels\Models;

use \JasterStary\RModels\RModel;
use \RedBeanPHP\R as R;

trait RepoTrait
{
    
  protected $repo = null;
  
  function setRepo($repo) {
    $this->repo = $repo;
  }
  
  function getRepo() {
    return $this->repo;
  }
  
  function toArray() {
    return $this->bean->export();
  }

}
