<?php

namespace AppBundle\Service;

use GraphCards\Db\Db;
use GraphCards\Db\DbAdapter;
use GraphCards\Db\DbConfig;
use Psr\Log\LoggerInterface;


class DbAdapterService
{
    /** @var string */
    protected $neo4jDefaultConnection = '';

    /** @var string */
    protected $neo4jBoltConnection = '';

    /** @var DbAdapter */
    protected $dbAdapter;

    /** @var LoggerInterface */
    protected $logger;


    public function __construct(string $neo4jDefaultConnection, string $neo4jBoltConnection, LoggerInterface $logger)
    {
        $this->neo4jDefaultConnection = $neo4jDefaultConnection;
        $this->neo4jBoltConnection = $neo4jBoltConnection;
        $this->logger = $logger;
    }


    public function getDbAdapter(): DbAdapter
    {
        if (! is_object($this->dbAdapter)) {
            $dbConfig = (new DbConfig())
                ->setDefaultConnection($this->neo4jDefaultConnection)
                ->setBoltConnection($this->neo4jBoltConnection)
                ->setLogger($this->logger);

            $db = new Db($dbConfig);

            $this->dbAdapter = new DbAdapter($db);
        }

        return $this->dbAdapter;
    }
}