<?php

namespace Ekyna\Bundle\OrderBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Sale\Order\OrderInterface;

/**
 * Class OrderEvent
 * @package Ekyna\Bundle\OrderBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderEvent extends ResourceEvent
{
    /**
     * Constructor.
     * 
     * @param \Ekyna\Component\Sale\Order\OrderInterface $order
     */
    public function __construct(OrderInterface $order)
    {
        $this->setResource($order);
    }

    /**
     * Returns the order.
     * 
     * @return \Ekyna\Component\Sale\Order\OrderInterface
     */
    public function getOrder()
    {
        return $this->getResource();
    }
}
