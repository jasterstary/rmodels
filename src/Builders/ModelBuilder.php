<?php

namespace JasterStary\RModels\Builders;

/**
 * Bridge class for generating R Models.
 *
 */
class ModelBuilder {

  protected array $using = [];

  protected string $fname = '';

  protected string $writable = '';

  function setModelPath(string $path): object {
    $path = realpath($path);
    if (!((is_string($path)) &&(is_dir($path)) && (is_writable($path)))) {
      throw new \Exception('Path is not accessible/writable: ' . $path);
    };
    $this->writable = $path;
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
    $fname = $this->writable . '/' . ucfirst($tableName) . '.php';
    if (is_file($fname)) {
      throw new \Exception('Class already exists.');
    };

    $this->s = '<?php' . "\n";
    $this->s.= 'namespace App\Models;' . "\n";
    $this->s.= "\n";
    $this->s.= 'use \JasterStary\RModels\RModel;' . "\n";
    $this->s.= 'use \RedBeanPHP\R as R;' . "\n";
    $this->s.= "\n";
    $this->s.= 'class ' . ucfirst($tableName) . ' extends RModel' . "\n";
    $this->s.= '{' . "\n";
    $this->s.= "\n";
    //$this->s.= '  use SiteRepoTrait;' . "\n"; //, LangModelTrait;
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

