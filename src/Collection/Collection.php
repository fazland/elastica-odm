<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Scroll;
use Elastica\SearchableInterface;
use Elastica\Type;
use Elastica\Type\Mapping;
use Elasticsearch\Endpoints;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Exception\RuntimeException;
use Fazland\ODM\Elastica\Search\Search;

class Collection implements CollectionInterface
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var SearchableInterface|Type
     */
    private $searchable;

    /**
     * @var null|string
     */
    private $_lastInsertId;

    public function __construct(string $documentClass, SearchableInterface $searchable)
    {
        $this->documentClass = $documentClass;
        $this->searchable = $searchable;
    }

    /**
     * {@inheritdoc}
     */
    public function scroll(Query $query, string $expiryTime = '1m'): Scroll
    {
        // Scroll requests have optimizations that make them faster when the sort order is _doc.
        // Add it to the query if no sort option have been defined.
        if (! $query->hasParam('sort')) {
            $query->setSort(['_doc']);
        }

        return $this->searchable->createSearch($query)->scroll($expiryTime);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Query $query): ResultSet
    {
        return $this->searchable->search($query);
    }

    /**
     * {@inheritdoc}
     */
    public function createSearch(DocumentManagerInterface $documentManager, Query $query): Search
    {
        $search = new Search($documentManager, $this->documentClass);
        $search->setQuery($query);

        return $search;
    }

    /**
     * {@inheritdoc}
     */
    public function count(Query $query): int
    {
        return $this->searchable->count($query);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(): void
    {
        $endpoint = new Endpoints\Indices\Refresh();

        try {
            $this->searchable->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(?string $id, array $body): Response
    {
        $endpoint = new Endpoints\Index();
        if (! empty($id)) {
            $endpoint->setParams(['op_type' => 'create']);
            $endpoint->setID($id);
        }

        $endpoint->setBody($body);
        try {
            $response = $this->searchable->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            $response = $exception->getResponse();
        }

        $data = $response->getData();
        if (! $response->isOk()) {
            throw new RuntimeException('Response not OK: '.$response->getErrorMessage());
        }

        if (isset($data['_id'])) {
            $this->_lastInsertId = $data['_id'];
        } else {
            $this->_lastInsertId = null;
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, array $body): void
    {
        $endpoint = new Endpoints\Update();
        $endpoint->setID($id);

        $endpoint->setBody([
            'doc' => $body,
        ]);

        try {
            $response = $this->searchable->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            $response = $exception->getResponse();
        }

        if (! $response->isOk()) {
            throw new RuntimeException('Response not OK: '.$response->getErrorMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): void
    {
        $endpoint = new Endpoints\Delete();
        $endpoint->setID($id);

        try {
            $response = $this->searchable->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            $response = $exception->getResponse();
        }

        if (! $response->isOk()) {
            throw new \RuntimeException('Response not OK: '.$response->getErrorMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInsertedId(): ?string
    {
        return $this->_lastInsertId;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMapping(Mapping $mapping): void
    {
        try {
            $response = $this->searchable->setMapping($mapping);
        } catch (ResponseException $exception) {
            $response = $exception->getResponse();
        }

        if (! $response->isOk()) {
            throw new \RuntimeException('Response not OK: '.$response->getErrorMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function drop(): void
    {
        $index = $this->searchable;
        if ($index instanceof Type) {
            $index = $index->getIndex();
        }

        $index->delete();
    }
}
