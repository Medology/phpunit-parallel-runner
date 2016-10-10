<?php namespace PhpUnit\Runner\Filter;

use InvalidArgumentException;
use PHPUnit_Framework_TestSuite;
use RecursiveIterator;
use RecursiveFilterIterator;

class Parallel extends RecursiveFilterIterator {
    private $THIS_NODE;
    private $TOTAL_NODES;
    private static $counter = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct(RecursiveIterator $iterator, $filter)
    {
        parent::__construct($iterator);
        $this->setFilter($filter[0], $filter[1]);
    }

    /**
     * @param $currentNode
     * @param $totalNodes
     */
    protected function setFilter($currentNode, $totalNodes)
    {
        if (!is_numeric($totalNodes) || $totalNodes < 1) {
            throw new InvalidArgumentException('Total nodes must be greater than or equal to 1!');
        } else if (!is_numeric($currentNode) || $currentNode < 0 || $currentNode >= $totalNodes) {
            throw new InvalidArgumentException(
                'Current node must be greater than or equal to 0, but less than or equal to total nodes!'
            );
        }

        $this->THIS_NODE = $currentNode;
        $this->TOTAL_NODES = $totalNodes;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof PHPUnit_Framework_TestSuite) {
            return true;
        }

        $mod = (self::$counter - $this->THIS_NODE) % $this->TOTAL_NODES;
        self::$counter++;

        return ($mod == 0);
    }
}