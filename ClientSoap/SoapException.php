<?php

/*
 * This file is part of HeriWebServiceBundle.
 *
 * (c) Alexandre Mogère
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Heri\Bundle\WebServiceBundle\ClientSoap;

class SoapException extends \Exception
{
    const TYPE_CONFIG  = 'conf';
    const TYPE_CONNECT = 'connect';
    const TYPE_CALL    = 'call';
    const TYPE_ANSWER  = 'answer'; 
    
    protected $errType  = null;
    protected $errMsg   = null;
    protected $name     = null;
    protected $data     = null;
    
    /**
    * @param  string problem source: config, connect, result
    * @param  string message
    * @param  ClientDispatcher $container
    */
    public function __construct($type, $message, ClientDispatcher $container = null)
    {
      $this->errType = $type;
      $this->errMsg  = $message;
      
      if (!is_null($container)) {
        $this->name    = $container->getClient()->getName();
        $this->data    = $container->getClient()->getData();
        
        $logger = $container->getLogger();
        $logger->err($this->getWsErr(true));
      }
      
      return parent::__construct($this->getWsErr(false));
    }
    
    /**
     * @access public
     * @return sting
     */
    public function __toString()
    {
        return $this->getWsErr(true);
    }
    
    /**
     * @params string verbose
     * @return string
     */
    public function getWsErr($verbose = false)
    {
        $msg = ($this->name ? '['.$this->name.']' : '') . '['.$this->errType.'] ' . $this->errMsg;
        if ($verbose && $this->data && ! empty($this->data)) {
            $msg .= "\n" . print_r($this->data, true);
        }
        
        return $msg;
    }
}