<?php

namespace PerrysLambda;

class Sortable
{

    const ORDER_ASC = "asc";
    const ORDER_DESC = "desc";

    protected $list;
    protected $orders;
    protected $comparator;

    /**
     * Create asc order instance
     * @param \PerrysLambda\ArrayList $list
     * @param callable $order
     * @return \PerrysLambda\Sortable
     */
    public static function startOrder(ArrayList $list, callable $order)
    {
        $temp = new static($list, $order, self::ORDER_ASC);
        $temp->thenBy($order);
        return $temp;
    }

    /**
     * Create desc order instance
     * @param \PerrysLambda\ArrayList $list
     * @param callable $order
     * @return \PerrysLambda\Sortable
     */
    public static function startOrderDesc(ArrayList $list, callable $order)
    {
        $temp = new static($list, $order, self::ORDER_DESC);
        $temp->thenByDesc($order);
        return $temp;
    }

    protected function __construct(ArrayList $list)
    {
        $this->list = $list;
        $this->orders = array();
        $this->setDefaultComparator();
    }

    /**
     * New asc order rule
     * @param callable $order
     * @return \PerrysLambda\Sortable
     */
    public function thenBy(callable $order)
    {
        $this->orders[] = array("property" => $order, "direction" => self::ORDER_ASC);
        return $this;
    }

    /**
     * New desc order rule
     * @param callable $order
     * @return \PerrysLambda\Sortable
     */
    public function thenByDesc(callable $order)
    {
        $this->orders[] = array("property" => $order, "direction" => self::ORDER_DESC);
        return $this;
    }

    /**
     * Set order comparator
     * @param callable $c
     * @return \PerrysLambda\Sortable
     */
    public function setComparator(callable $c)
    {
        $this->comparator = $c;
        return $this;
    }

    /**
     * Get current comparator
     * @return callable
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * Set the default compare method
     */
    protected function setDefaultComparator()
    {
        $func = function($a, $b)
        {
            $cmpres = null;
            foreach($this->orders as $order)
            {
                $valuea = call_user_func($order['property'], $a);
                $valueb = call_user_func($order['property'], $b);

                if($valuea instanceof \DateTime || $valueb instanceof \DateTime ||
                      is_numeric($valuea) || is_numeric($valueb))
                {
                   if(is_numeric($valuea) || is_numeric($valueb))
                   {
                      $valuea = ((double)$valuea);
                      $valueb = ((double)$valueb);
                   }
                   $cmpres = ($valuea < $valueb ? -1 : ($valuea > $valueb ? 1 : 0));
                }
                else
                {
                   $cmpres = strcmp($valuea, $valueb);
                }

                $temp = (($order['direction'] == self::ORDER_DESC) ? -1 : 1) * $cmpres;
                if($temp==0) continue;
                return $temp;
            }
            return null;
        };

        $this->setComparator($func);
    }

    /**
     * Sort and get the arraylist
     * @return \PerrysLambda\ArrayList
     */
    public function toList()
    {
        $data = $this->list->getData();
        usort($data, $this->getComparator());
        $this->list->setData($data);
        return $this->list;
    }

}
