<?php

namespace JasterStary\RModels\Adapters;

use \CodeIgniter\Session\SessionInterface;
/*
 *
 * new RCodeigniterSession(\Config\Services::session());
 *
 */

class RCodeigniterSession implements RSession
{

  /**
  * @var CodeIgniter\Session\SessionInterface $session
  */
  private \CodeIgniter\Session\SessionInterface $session;

  /**
  * @var string $prefix
  */
  private string $prefix;

  /**
  * constructor
  *
  * @param CodeIgniter\Session\SessionInterface $session
  * @param string $prefix
  */
  function __construct(object $session, string $prefix='') {
    $this->session = $session;
    $this->prefix = $prefix;
  }

  /**
  * get var
  *
  * @param string $key
  * @return mixed
  */
  function getVar(string $key): mixed {
    if (!$this->session->has($this->prefix . $key)) return null;
    return $this->session->get($this->prefix . $key);
  }

  /**
  * set var
  *
  * @param string $key
  * @param mixed $value
  * @return void
  */
  function setVar(string $key, mixed $value):void {
    $this->session->set($this->prefix . $key, $value);
  }

}
