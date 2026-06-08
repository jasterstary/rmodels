<?php

namespace JasterStary\RModels\Adapters;

/*
 * example:
 * new RCodeigniterSession(\Config\Services::session());
 *
 */

interface RSession
{

  public function __construct(object $session, string $prefix='');

  public function getVar(string $key):mixed;

  function setVar(string $key, mixed $value):void;

}
