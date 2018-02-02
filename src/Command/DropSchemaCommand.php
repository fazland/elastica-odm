<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Command;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DropSchemaCommand extends Command
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('drop-schema');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elastica ODM - drop schema');

        $io->caution('This operation will drop all the indices defined in your mapping.');
        if (! $io->confirm('Are you sure you want to continue?')) {
            return;
        }

        $factory = $this->documentManager->getMetadataFactory();

        /** @var DocumentMetadata $metadata */
        foreach ($factory->getAllMetadata() as $metadata) {
            $collection = $this->documentManager->getCollection($metadata->getName());
            $collection->drop();
        }

        $io->success('All done.');
    }
}
