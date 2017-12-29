<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Hydrator;

use Doctrine\Instantiator\Instantiator;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\ResultSet;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator\Internal\ProxyInstantiator;

class ObjectHydrator implements HydratorInterface
{
    /**
     * @var DocumentManagerInterface
     */
    private $manager;

    public function __construct(DocumentManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateAll(ResultSet $resultSet, string $className): array
    {
        if (0 === $resultSet->count()) {
            return [];
        }

        $class = $this->manager->getClassMetadata($className);

        try {
            $source = $resultSet->getQuery()->getParam('_source');
        } catch (InvalidException $ex) {
            $source = null;
        }

        if (null !== $source) {
            $fields = false === $source ? [] : $source;
            $instantiator = new ProxyInstantiator($fields, $this->manager);
        } else {
            $instantiator = $this->getInstantiator();
        }

        $results = [];

        foreach ($resultSet as $result) {
            $document = $result->getDocument();
            $object = $this->manager->getUnitOfWork()->tryGetById($document->getId(), $class);

            if (null === $object) {
                $object = $instantiator->instantiate($className);
                $this->manager->getUnitOfWork()->createDocument($document, $object);
            }

            $results[] = $object;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateOne(Document $document, string $className)
    {
        $result = $this->getInstantiator()->instantiate($className);
        $this->manager->getUnitOfWork()->createDocument($document, $result);

        return $result;
    }

    private function getInstantiator(): Instantiator
    {
        static $instantiator = null;
        if (null === $instantiator) {
            $instantiator = new Instantiator();
        }

        return $instantiator;
    }
}
