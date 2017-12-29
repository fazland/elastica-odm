<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Id;

final class PostInsertId
{
    /**
     * The document for which the id has been created.
     *
     * @var object
     */
    private $document;

    /**
     * The returned id.
     *
     * @var string
     */
    private $id;

    public function __construct($document, string $id)
    {
        $this->document = $document;
        $this->id = $id;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getId(): string
    {
        return $this->id;
    }
}