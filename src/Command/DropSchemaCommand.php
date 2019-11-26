<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Command;

use Elasticsearch\Endpoints;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Exception\CannotDropAnAliasException;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    protected function configure(): void
    {
        $this
            ->setName('drop-schema')
            ->addOption('with-aliases', null, InputOption::VALUE_NONE, 'Drop also aliases with corresponding name alongside of the aliased indexes')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elastica ODM - drop schema');

        $io->caution('This operation will drop all the indices defined in your mapping.');
        if (! $io->confirm('Are you sure you want to continue?')) {
            return 0;
        }

        $factory = $this->documentManager->getMetadataFactory();

        /** @var DocumentMetadata $metadata */
        foreach ($factory->getAllMetadata() as $metadata) {
            $collection = $this->documentManager->getCollection($metadata->getName());
            try {
                $collection->drop();
            } catch (CannotDropAnAliasException $e) {
                if ($input->getOption('with-aliases')) {
                    $this->dropAlias(\explode('/', $collection->getName())[0]);
                } else {
                    $io->warning([
                        $collection->getName().' is an alias.',
                        'Pass --with-aliases option to drop the alias too.',
                    ]);
                }
            }
        }

        $io->success('All done.');

        return 0;
    }

    private function dropAlias(string $aliasName): void
    {
        $connection = $this->documentManager->getDatabase()->getConnection();
        $response = $connection->requestEndpoint((new Endpoints\Indices\Alias\Get())->setName($aliasName));

        foreach (\array_keys($response->getData()) as $indexName) {
            $connection->requestEndpoint((new Endpoints\Indices\Delete())->setIndex($indexName));
        }
    }
}
