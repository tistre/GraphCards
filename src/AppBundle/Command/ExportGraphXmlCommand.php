<?php

namespace AppBundle\Command;

use AppBundle\Service\DbAdapterService;
use GraphCards\Db\DbQuery;
use GraphCards\Model\Node;
use GraphCards\Model\Relationship;
use GraphCards\Utils\XmlExporter;
use GraphCards\Utils\XmlReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ExportGraphXmlCommand extends Command
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
            ->setName('app:export-graph-xml')
            ->setDescription('Exports Graph XML to a file')
            ->setHelp('Export to a file')
            ->addArgument('query', InputArgument::REQUIRED,
                'Cypher query for nodes to export. Returned node variable name must be "node".');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbAdapter = $this->dbAdapterService->getDbAdapter();

        $dbQuery = (new DbQuery())
            ->setQuery($input->getArgument('query'));

        $xmlExporter = new XmlExporter('php://output');
        $xmlExporter->startDocument();

        foreach ($dbAdapter->listResults($dbQuery) as $rowNum => $row) {
            $rowData = [];

            foreach ($row as $columnName => $obj) {
                if (is_object($obj)) {
                    if ($obj instanceof Node) {
                        $xmlExporter->exportNode($obj, ['rowNumber' => $rowNum, 'columnName' => $columnName]);
                    } elseif ($obj instanceof Relationship) {
                        $xmlExporter->exportRelationship($obj, ['rowNumber' => $rowNum, 'columnName' => $columnName]);
                    } else {
                        throw new \RuntimeException(
                            sprintf(
                                '%s: Unsupported object type <%s>.',
                                __METHOD__,
                                get_class($obj)
                            )
                        );
                    }
                } else {
                    $rowData[$columnName] = (string)$obj;
                }
            }

            if (count($rowData) > 0) {
                // <row>
                $xmlExporter->writer->startElement('row');
                $xmlExporter->writer->writeAttribute('rowNumber', $rowNum);

                foreach ($rowData as $columnName => $value) {
                    // <record></record>
                    $xmlExporter->writer->startElement('record');
                    $xmlExporter->writer->writeAttribute('columnName', $columnName);
                    $xmlExporter->writer->text($value);
                    $xmlExporter->writer->endElement();
                }

                // </row>
                $xmlExporter->writer->endElement();
            }
        }

        $xmlExporter->endDocument();
    }
}