<?php

namespace JasterStary\RModels\Adapters;

use \Illuminate\Contracts\Session\Session;
/*
 *
 * new RLaravelSession($request->session());
 *
 */

class RLaravelSession implements RSession
{
  private \Illuminate\Contracts\Session\Session $session;
  private string $prefix = '';

  function __construct(\Illuminate\Contracts\Session\Session $session, string $prefix = '') {
    $this->session = $session;
    $this->prefix = $prefix;
  }

  function getVar(string $key): mixed {
    if (!$this->session->has($this->prefix . $key)) return null;
    return $this->session->get($this->prefix . $key);
  }

  function setVar(string $key, mixed $value) {
    $this->session->put($this->prefix . $key, $value);
  }

}
