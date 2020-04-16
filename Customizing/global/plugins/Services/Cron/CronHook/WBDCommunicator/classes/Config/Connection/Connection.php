<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Connection;

class Connection
{
    protected $host;
    protected $port;
    protected $endpoint;
    protected $namespace;
    protected $name;

    public function __construct(
        string $host,
        string $port,
        string $endpoint,
        string $namespace,
        string $name
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->endpoint = $endpoint;
        $this->namespace = $namespace;
        $this->name = $name;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPort() : string
    {
        return $this->port;
    }

    public function getEndpoint() : string
    {
        return $this->endpoint;
    }

    public function getNamespace() : string
    {
        return $this->namespace;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function withHost(string $host) : Connection
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function withPort(string $port) : Connection
    {
        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function withEndpoint(string $endpoint) : Connection
    {
        $clone = clone $this;
        $clone->endpoint = $endpoint;
        return $clone;
    }

    public function withNamespace(string $namespace) : Connection
    {
        $clone = clone $this;
        $clone->namespace = $namespace;
        return $clone;
    }

    public function withName(string $name) : Connection
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }
}
