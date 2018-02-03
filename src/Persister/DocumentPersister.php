<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Persister;

use Elastica\Query;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Exception\ConversionFailedException;
use Fazland\ODM\Elastica\Hydrator\HydratorInterface;
use Fazland\ODM\Elastica\Id\PostInsertId;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;

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

    public function __construct(DocumentManagerInterface $dm, DocumentMetadata $class)
    {
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
     * @param array  $hints
     * @param object $document The document to load data into. If not given, a new document will be created.
     *
     * @return object|null the loaded and managed document instance or null if no document was found
     */
    public function load(array $criteria, array $hints = [], $document = null)
    {
        $query = $this->prepareQuery($criteria);
        if ($hints[Hints::HINT_REFRESH] ?? false) {
            $params = $query->getParams();
            unset($params['_source']);
            $query->setParams($params);
        }

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
        $search = $this->collection->createSearch($this->dm, $query);
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
        $query->setSize(0);
        $query->setParam('terminate_after', 1);

        return $this->collection->search($query)->count() > 0;
    }

    /**
     * Insert a document in the collection.
     *
     * @param object $document
     *
     * @return PostInsertId|null
     */
    public function insert($document): ?PostInsertId
    {
        /** @var DocumentMetadata $class */
        $class = $this->dm->getClassMetadata(get_class($document));
        $idGenerator = $this->dm->getUnitOfWork()->getIdGenerator($class->idGeneratorType);
        $postIdGenerator = $idGenerator->isPostInsertGenerator();

        $id = $postIdGenerator ? null : $class->getSingleIdentifier($document);
        $body = $this->prepareUpdateData($document);

        $response = $this->collection->create($id, $body);
        $data = $response->getData();

        foreach ($class->attributesMetadata as $field) {
            if (! $field instanceof FieldMetadata) {
                continue;
            }

            if ($field->indexName) {
                $field->setValue($document, $data['_index'] ?? null);
            }

            if ($field->typeName) {
                $field->setValue($document, $data['_type'] ?? null);
            }
        }

        $postInsertId = null;
        if ($postIdGenerator) {
            $postInsertId = new PostInsertId($document, $this->collection->getLastInsertedId());
        }

        return $postInsertId;
    }

    /**
     * Updates a managed document.
     *
     * @param object $document
     */
    public function update($document): void
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        $data = $this->prepareUpdateData($document);
        $id = $class->getSingleIdentifier($document);

        $this->collection->update((string) $id, $data['body'], $data['script']);
    }

    /**
     * Deletes a managed document.
     *
     * @param object $document
     */
    public function delete($document): void
    {
        $class = $this->dm->getClassMetadata(get_class($document));
        $id = $class->getSingleIdentifier($document);

        $this->collection->delete((string) $id);
    }

    /**
     * Refreshes the underlying collection.
     */
    public function refreshCollection(): void
    {
        $this->collection->refresh();
    }

    private function prepareQuery(array $criteria): Query
    {
        $bool = new Query\BoolQuery();
        foreach ($criteria as $key => $value) {
            $bool->addFilter(new Query\Term([$key => ['value' => $value]]));
        }

        return Query::create($bool);
    }

    /**
     * INTERNAL:
     * Prepares data for an update operation.
     *
     * @param object $document
     *
     * @return array
     *
     * @internal
     *
     * @throws ConversionFailedException
     */
    public function prepareUpdateData($document): array
    {
        $script = [];
        $body = [];

        $changeSet = $this->dm->getUnitOfWork()->getDocumentChangeSet($document);
        $class = $this->dm->getClassMetadata(get_class($document));
        $typeManager = $this->dm->getTypeManager();

        foreach ($changeSet as $name => $value) {
            $field = $class->attributesMetadata[$name];
            $type = $typeManager->getType($field->type);

            if ($field->multiple) {
                $body[$field->fieldName] = array_map(function ($item) use ($type, $field) {
                    return $type->toDatabase($item, $field->options);
                }, (array) $value[1]);
            } elseif (null !== $value[1]) {
                $body[$field->fieldName] = $type->toDatabase($value[1], $field->options);
            } else {
                $script[] = 'ctx._source.remove(\''.str_replace('\'', '\\\'', $field->fieldName).'\')';
            }
        }

        return [
            'body' => $body,
            'script' => implode('; ', $script),
        ];
    }
}
