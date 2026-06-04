<?php

namespace JasterStary\RModels\Builders;

/**
 * Bridge class for generating R Models.
 *
 */
class Builder {

  protected RepoBuilder $repoBuilder;

  protected ModelBuilder $modelBuilder;

  protected FixtureBuilder $fixtureBuilder;

  public function __construct($repoPath, $modelPath, $fixturePath) {
    $this->repoBuilder = new RepoBuilder();
    $this->modelBuilder = new ModelBuilder();
    $this->fixtureBuilder = new FixtureBuilder();
    $this->repoBuilder->setRepoPath($repoPath);
    $this->modelBuilder->setModelPath($modelPath);
    $this->fixtureBuilder->setFixturePath($fixturePath);
  }

  public function Repo() {
    return $this->repoBuilder;
  }

  public function Model() {
    return $this->modelBuilder;
  }

  public function Fixture() {
    return $this->fixtureBuilder;
  }

  /**
  *
  * @param string $tableName
  * @param int $cnt
  * @return array<mixed>
  */
  public function generate(string $tableName):array {
    $re = [];
    try {
      $re['repo'] = $this->repoBuilder->generate($tableName);
    } catch(\Throwable $e) {
      $re['repo'] = $e->getMessage();

    }
    try {
      $re['model'] = $this->modelBuilder->generate($tableName);
    } catch(\Throwable $e) {
      $re['model'] = $e->getMessage();

    }
    try {
      $re['fixture'] = $this->fixtureBuilder->generate($tableName);
    } catch(\Throwable $e) {
      $re['fixture'] = $e->getMessage();

    }
    return $re;
  }


}

