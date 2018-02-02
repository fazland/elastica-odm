<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Command;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Tools\SchemaGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateSchemaCommand extends Command
{
    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    public function __construct(DocumentManagerInterface $documentManager)
    {
        $this->documentManager = $documentManager;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('update-schema');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elastica ODM - update schema');

        $generator = new SchemaGenerator($this->documentManager);
        $schema = $generator->generateSchema();

        foreach ($schema->getMapping() as $className => $mapping) {
            $collection = $this->documentManager->getCollection($className);
            $collection->updateMapping($mapping);
        }

        $io->success('All done.');
    }
}
