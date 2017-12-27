<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;

final class UnitOfWork
{
    private $identityMap = [];

    private $objects = [];

    /**
     * @var DocumentManagerInterface
     */
    private $manager;

    public function __construct(DocumentManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Searches for a document in the identity map and returns it if found.
     * Returns null otherwise.
     *
     * @param string $className
     * @param mixed $id
     *
     * @return object|null
     */
    public function tryGetById(string $className, $id)
    {
        $metadata = $this->manager->getClassMetadata($className);

        return $this->identityMap[$metadata->name][(string) $id] ?? null;
    }

    /**
     * Adds a document to the identity map.
     * The identifier MUST be set before trying to add the document or
     * this method will throw an InvalidIdentifierException.
     *
     * @param $object
     *
     * @throws InvalidIdentifierException
     */
    public function addToIdentityMap($object)
    {
        $oid = spl_object_hash($object);
        if (isset($this->objects[$oid])) {
            return;
        }

        $this->objects[$oid] = $object;

        $metadata = $this->manager->getClassMetadata($object);
        $id = $metadata->getIdentifierValues($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        $this->identityMap[$metadata->name][implode(' ', $id)] = $object;
    }

    /**
     * Checks if a document is attached to this unit of work.
     *
     * @param $object
     *
     * @return bool
     */
    public function isInIdentityMap($object): bool
    {
        $oid = spl_object_hash($object);
        if (! isset($this->objects[$oid])) {
            return false;
        }

        $metadata = $this->manager->getClassMetadata($object);
        $id = $metadata->getIdentifierValues($object);

        if (empty($id)) {
            return false;
        }

        return isset($this->identityMap[$metadata->name][$id]);
    }
}
