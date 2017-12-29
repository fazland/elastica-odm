<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Persister;

use Elastica\Query;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator\HydratorInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

class DocumentPersister
{
    /**
     * @var DocumentManagerInterface
     */
    private $dm;

    /**
     * @var DocumentMetadata
     */
    private $class;

    /**
     * @var CollectionInterface
     */
    private $collection;

    public function __construct(
        DocumentManagerInterface $dm,
        DocumentMetadata $class
    ) {
        $this->dm = $dm;
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

        return $this->dm->newHydrator(HydratorInterface::HYDRATE_OBJECT)
            ->hydrateOne($esDoc, $this->class->name);
    }

    public function loadAll(array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $query = $this->prepareQuery($criteria);
        $search = $this->collection->createSearch($query);
        $search->setSort($orderBy);

        if (null === $limit && null === $offset) {
            $search->setScroll(true);
        } else {
            if (null !== $limit) {
                $search->setLimit($limit);
            }

            if (null !== $offset) {
                $search->setOffset($offset);
            }
        }

        return $search->execute();
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
