<?php

namespace HuubVerbeek\ConsistentHashing;

use Closure;
use HuubVerbeek\ConsistentHashing\Traits\Validator;

/**
 * @property NodeCollection $nodeCollection
 */
class ConsistentHashingService
{
    use Validator;

    /**
     * @param  NodeCollection  $nodeCollection
     */
    public function __construct(public NodeCollection $nodeCollection)
    {
        //
    }

    /**
     * @param  string  $string
     * @return int
     */
    public function getDegree(string $string): int
    {
        $hex = md5($string);

        $dec = hexdec($hex) / pow(10, 25);

        return $dec % 360;
    }

    /**
     * @param  int  $degree
     * @return Closure
     */
    public function degreeEqualOrSmallerThan(int $degree): Closure
    {
        return fn ($value, $key) => $this->getDegree($key) <= $degree;
    }

    /**
     * @param  string  $key
     * @return AbstractNode
     */
    public function resolve(string $key): AbstractNode
    {
        $degree = $this->getDegree($key);

        return $this->nextNode($degree);
    }

    /**
     * @param  int  $degree
     * @return AbstractNode
     */
    public function nextNode(int $degree): AbstractNode
    {
        return $this->nodeCollection->next($degree);
    }

    /**
     * @param  int  $degree
     * @return AbstractNode
     */
    public function previousNode(int $degree): AbstractNode
    {
        return $this->nodeCollection->previous($degree);
    }

    /**
     * @param  AbstractNode  $node
     * @return NodeCollection
     */
    public function addNode(AbstractNode $node): NodeCollection
    {
        if ($this->nodeCollection->wantsRekey()) {
            $next = $this->nextNode($node->degree + 1);
            $this->moveItems($next, $node, $this->degreeEqualOrSmallerThan($node->degree));
        }

        $this->nodeCollection->add($node);

        return $this->nodeCollection;
    }

    /**
     * @param  string  $identifier
     * @return NodeCollection
     */
    public function removeNode(string $identifier): NodeCollection
    {
        $node = $this->nodeCollection->findByIdentifier($identifier);

        if ($this->nodeCollection->wantsRekey()) {
            $next = $this->nextNode($node->degree + 1);
            $this->moveItems($node, $next);
        }

        $this->nodeCollection->remove($identifier);

        return $this->nodeCollection;
    }

    /**
     * @param  AbstractNode  $from
     * @param  AbstractNode  $target
     * @param  Closure|null  $filter
     * @return void
     */
    public function moveItems(AbstractNode $from, AbstractNode $target, ?Closure $filter = null): void
    {
        $items = $from->all();

        if ($filter) {
            $items = $items->filter($filter);
        }

        $items->each($from->moveItemTo($target));
    }
}
