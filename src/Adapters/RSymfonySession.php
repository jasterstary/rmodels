<?php

namespace JasterStary\RModels\Adapters;

/*
 * 
 * new RSymfonySession($request->getSession());
 * 
 */
 
class RSymfonySession
{
    
  function _construct($session, $prefix = '') {
    $this->session = $session;
    $this->prefix = $prefix;
  }

  function getVar($key) {
    return $this->session->get($this->prefix . $key);
  }

  function setVar($key, $value) {
    $this->session->set($this->prefix . $key, $value);
  }
  
}
