<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Persister;

use Elastica\Query;
use Elastica\SearchableInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

class DocumentPersister
{
    /**
     * @var DocumentManagerInterface
     */
    private $dm;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var DocumentMetadata
     */
    private $class;

    /**
     * @var SearchableInterface
     */
    private $collection;

    public function __construct(
        DocumentManagerInterface $dm,
        DocumentMetadata $class,
        Hydrator $hydrator
    ) {
        $this->dm = $dm;
        $this->hydrator = $hydrator;

        $this->class = $class;
        $this->collection = $dm->getCollection($class->name);
    }

    /**
     * @return DocumentMetadata
     */
    public function getClassMetadata(): DocumentMetadata
    {
        return $this->class;
    }

    /**
     * Finds a document by a set of criteria.
     *
     * @param array  $criteria query criteria
     * @param object $document The document to load data into. If not given, a new document will be created.
     *
     * @return object|null the loaded and managed document instance or null if no document was found
     */
    public function load(array $criteria, $document = null)
    {
        $query = $this->prepareQuery($criteria);
        $resultSet = $this->collection->search($query);

        if (! count($resultSet)) {
            return null;
        }

        $esDoc = $resultSet[0]->getDocument();

        if (null !== $document) {
            $this->dm->getUnitOfWork()->createDocument($esDoc, $document);

            return $document;
        }

        return $this->hydrator->hydrateOne($esDoc, $this->class->name);
    }

    /**
     * Checks whether a document matching criteria exists in collection.
     *
     * @param array $criteria
     *
     * @return bool
     */
    public function exists(array $criteria): bool
    {
        $query = $this->prepareQuery($criteria);

        return $this->collection->count($query) > 0;
    }

    private function prepareQuery(array $criteria): Query
    {
        $bool = new Query\BoolQuery();
        foreach ($criteria as $key => $value) {
            $bool->addFilter(new Query\Term([$key => ['value' => $value]]));
        }

        return Query::create($bool);
    }
}
