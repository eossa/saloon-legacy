<?php

namespace Saloon\Traits\Connector;

use Saloon\Config;
use Saloon\Contracts\Sender;

trait HasSender
{
    /**
     * Specify the default sender
     *
     * @var string
     */
    protected $defaultSender = '';

    /**
     * The request sender.
     *
     * @var Sender
     */
    protected $sender;

    /**
     * Manage the request sender.
     *
     * @return Sender
     */
    public function sender()
    {
        if (isset($this->sender)) {
            return $this->sender;
        }
        return $this->sender = $this->defaultSender();
    }

    /**
     * Define the default request sender.
     *
     * @return Sender
     */
    protected function defaultSender()
    {
        if (empty($this->defaultSender)) {
            return Config::getDefaultSender();
        }

        return new $this->defaultSender;
    }
}
