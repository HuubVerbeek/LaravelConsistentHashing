<?php

namespace HuubVerbeek\ConsistentHashing;

use HuubVerbeek\ConsistentHashing\Exceptions\NodeCollectionException;
use HuubVerbeek\ConsistentHashing\Rules\NodeCollectionRule;

class ForwarderNodeCollection extends NodeCollection
{
    /**
     * @param  array  $nodes
     *
     * @throws \Throwable
     */
    public function __construct(array $nodes)
    {
        $this->validate(
            new NodeCollectionRule($nodes, ForwarderNode::class),
            new NodeCollectionException(ForwarderNode::class)
        );

        parent::__construct($nodes);
    }

    /**
     * @return bool
     */
    public function wantsRekey(): bool
    {
        return false;
    }
}
