<?php

namespace JasterStary\RModels\Builders;

/**
 * Bridge class for generating R Repo Fixtures.
 *
 */
class FixtureBuilder {

  protected array $columns = [
    'slug' => 'text|alpha_numeric',
    'title' => 'text|string',
    'description' => 'text|string',
    'deleted' => 'date',
  ];

  protected array $hardConditions = [];

  protected array $using = [];

  protected string $fname = '';

  protected string $writable = '';

  function setFixturePath(string $path): object {
    $path = realpath($path);
    if (!((is_string($path)) &&(is_dir($path)) && (is_writable($path)))) {
      throw new \Exception('Path is not accessible/writable: ' . $path);
    };
    $this->writable = $path;
    return $this;
  }

  /**
  * set columns
  *
  * @param array $columns
  * @return object
  */
  public function setColumns(array $columns): object {
    $this->columns = $columns;
    return $this;
  }

  /**
  * set columns
  *
  * @param array $columns
  * @return object
  */
  public function setHardConditions(array $columns): object {
    $this->hardConditions = $columns;
    return $this;
  }

  public function setUsing(array $using): object {
    $this->using = $using;
    return $this;
  }

  /**
  *
  * @param string $tableName
  * @param int $cnt
  * @return array<mixed>
  */
  public function generate(string $tableName):array {
    if (!is_dir($this->writable)) {
      throw new \Exception('Writable folder exception.');
    };
    $fname = $this->writable . '/' . ucfirst($tableName) . 'Fixture.php';
    if (is_file($fname)) {
      throw new \Exception('Class already exists.');
    };

    $this->s = '<?php' . "\n";
    $this->s.= 'namespace App\Fixtures;' . "\n";
    $this->s.= "\n";
    $this->s.= 'use \JasterStary\RModels\RFixture;' . "\n";
    $this->s.= 'use \RedBeanPHP\R as R;' . "\n";
    $this->s.= "\n";
    $this->s.= 'class ' . ucfirst($tableName) . 'Fixture extends RFixture' . "\n";
    $this->s.= '{' . "\n";
    $this->s.= "\n";
    $this->s.= '  protected static string $table = \'' . $tableName . '\';' . "\n";
    $this->s.= "\n";
    $this->s.= "\n";
    $this->s.= '}' . "\n";

    file_put_contents($fname, $this->s);
    $complete = [
      'fileName' => $fname,
    ];
    return $complete;

  }


}

