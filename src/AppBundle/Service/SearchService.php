<?php

namespace AppBundle\Service;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;


class SearchService
{
    /** @var string */
    protected $elasticsearchConnection = '';

    /** @var LoggerInterface */
    protected $logger;

    /** @var Client */
    protected $client;


    public function __construct(string $elasticsearchConnection, LoggerInterface $logger)
    {
        $this->elasticsearchConnection = $elasticsearchConnection;
        $this->logger = $logger;
    }


    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()->setLogger($this->logger)->build();
        }

        return $this->client;
    }
}