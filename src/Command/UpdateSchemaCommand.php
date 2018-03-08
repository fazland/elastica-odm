<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Command;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Tools\SchemaGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('update-schema');
        $this->addOption('filter-expression', null, InputOption::VALUE_REQUIRED, 'Filters the classes for schema updates via a regular expression');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elastica ODM - update schema');

        $generator = new SchemaGenerator($this->documentManager);
        $schema = $generator->generateSchema();

        $expression = $input->getOption('filter-expression');
        if (null !== $expression) {
            if (false === preg_match($expression, '')) {
                throw new \InvalidArgumentException('Filter expression is not a valid regex');
            }

            $filter = function ($value) use ($expression): bool {
                return (bool) preg_match($expression, $value);
            };
        } else {
            $filter = function (): bool {
                return true;
            };
        }

        foreach ($schema->getMapping() as $className => $mapping) {
            if (! $filter($className)) {
                continue;
            }

            $collection = $this->documentManager->getCollection($className);
            $collection->updateMapping($mapping->getMapping());
        }

        $io->success('All done.');
    }
}
