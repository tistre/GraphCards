<?php

namespace AppBundle\Utils;


use GraphCards\Model\Node;

class NodeSearchResult
{
    /** @var Node[] */
    protected $nodes = [];

    /** @var int */
    protected $totalHits = 0;

    /** @var int */
    protected $offset = 0;


    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }


    /**
     * @param Node[] $nodes
     * @return self
     */
    public function setNodes(array $nodes): NodeSearchResult
    {
        $this->nodes = $nodes;
        return $this;
    }


    /**
     * @return int
     */
    public function getTotalHits(): int
    {
        return $this->totalHits;
    }


    /**
     * @param int $totalHits
     * @return self
     */
    public function setTotalHits(int $totalHits): NodeSearchResult
    {
        $this->totalHits = $totalHits;
        return $this;
    }


    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }


    /**
     * @param int $offset
     * @return self
     */
    public function setOffset(int $offset): NodeSearchResult
    {
        $this->offset = $offset;
        return $this;
    }


    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return ($this->offset > 0);
    }


    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return (($this->offset + count($this->nodes)) < $this->totalHits);
    }
}