<?php

namespace AppBundle\Command;

use AppBundle\Service\DbAdapterService;
use GraphCards\Model\Node;
use GraphCards\Model\Relationship;
use GraphCards\Utils\XmlReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ImportGraphXmlCommand extends Command
{
    /** @var DbAdapterService */
    protected $dbAdapterService;


    public function __construct(DbAdapterService $dbAdapterService)
    {
        parent::__construct();

        $this->dbAdapterService = $dbAdapterService;
    }


    protected function configure()
    {
        $this
            ->setName('app:import-graph-xml')
            ->setDescription('Imports Graph XML from a file')
            ->setHelp('Import from a file')
            ->addArgument('input', InputArgument::REQUIRED, 'Graph XML input file name');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbAdapter = $this->dbAdapterService->getDbAdapter();

        $output->writeln('Importing Graph XML from ' . $input->getArgument('input'));

        $objects = new XmlReader($input->getArgument('input'));

        foreach ($objects as $object) {
            if (!is_object($object)) {
                continue;
            }

            if ($object instanceof Node) {
                try {
                    $node = $dbAdapter->createNode($object);
                    $output->writeln(sprintf('Created :%s node <%s>', implode(':', $node->getLabels()),
                        $node->getUuid()));
                } catch (\Exception $e) {
                    $output->writeln('Error creating node: ' . print_r($object, true));
                }
            } elseif ($object instanceof Relationship) {
                try {
                    $relationship = $dbAdapter->createRelationship($object);
                    $output->writeln(sprintf('Created :%s relationship <%s>', $relationship->getType(),
                        $relationship->getUuid()));
                } catch (\Exception $e) {
                    $output->writeln('Error creating relationship: ' . print_r($object, true));
                }
            }
        }
    }
}