<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;

class RFixture {

  protected static string $table = 'basic';

  protected $repo = null;

  function __construct($repo = null) {
    /*
    if ($repo === null) {
      $repoName = ucfirst(static::$table) . 'Repo';
      $repo = new $repoName();
    }
    */
    $this->repo = $repo;
    $this->faker = \Faker\Factory::create();
  }

  function setRepo($repo) {
    $this->repo = $repo;
    return $this;
  }

  function generate(int $cnt = 1, array $unique = []) {

    $columns = $this->repo->getColumns();
    $bHasSlug = false;
    for ($i=0; $i<$cnt; $i++) {
      $data = [];
      foreach ($columns as $k => $v) {
        if ($k=='slug') {
          $bHasSlug = true;
          continue;
        }
        $types = explode('|', $v);
        $method = 'get' . ucfirst($k);
        if (method_exists($this, $method)) {
          $data[$k] = $this->$method($data);
        }
      }
      if ($bHasSlug) {
          if (method_exists($this, 'getSlug')) {
            $data['slug'] = $this->getSlug($data);
          }
          if (!in_array('slug', $unique)) $unique[] = 'slug';
      }
      if (!empty($data)) {
        $this->repo->Create($data, $unique);
      }
    }



  }

  function getSlug($data) {
    if (isset($data['title'])) {
      return RRepo::slugify($data['title']);

    } else if(isset($data['name'])) {
      return RRepo::slugify($data['name']);

    } else return $this->faker->word();
  }

  function getTitle() {return $this->faker->word(); }

  function getName() {return $this->faker->name(); }

  function getDescription() {return $this->faker->paragraph(3); }




}
