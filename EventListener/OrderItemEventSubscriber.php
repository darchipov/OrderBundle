<?php

namespace Ekyna\Bundle\OrderBundle\EventListener;

use Ekyna\Bundle\OrderBundle\Event\OrderItemEvent;
use Ekyna\Bundle\OrderBundle\Event\OrderEvents;
use Ekyna\Bundle\OrderBundle\Event\OrderItemEvents;
use Ekyna\Bundle\OrderBundle\Exception\LogicException;
use Ekyna\Bundle\OrderBundle\Helper\ItemHelperInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class OrderItemEventSubscriber
 * @package Ekyna\Bundle\OrderBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemEventSubscriber extends AbstractEventSubscriber
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ItemHelperInterface
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param ItemHelperInterface $helper
     */
    public function __construct(EventDispatcherInterface $dispatcher, ItemHelperInterface $helper)
    {
        $this->dispatcher = $dispatcher;
        $this->helper     = $helper;
    }

    /**
     * Pre item add event handler.
     *
     * @param OrderItemEvent $event
     * @throws LogicException
     */
    public function onPreAdd(OrderItemEvent $event)
    {
        if ($this->isOrderLocked($event)) {
            return;
        }

        $this->prepareEvent($event);
    }

    /**
     * Item add event handler.
     *
     * @param OrderItemEvent $event
     */
    public function onAdd(OrderItemEvent $event)
    {
        $order = $event->getOrder();
        $item  = $event->getItem();

        $order->addItem($item);
    }

    /**
     * Post item add event handler.
     *
     * @param OrderItemEvent $event
     */
    public function onPostAdd(OrderItemEvent $event)
    {
        $this->dispatcher->dispatch(OrderEvents::CONTENT_CHANGE, $event);
    }

    /**
     * Pre item sync event handler.
     *
     * @param OrderItemEvent $event
     * @throws LogicException
     */
    public function onPreSync(OrderItemEvent $event)
    {
        if ($this->isOrderLocked($event)) {
            return;
        }

        if (!$event->hasItem()) {
            throw new LogicException('Item must be set.');
        }
    }

    /**
     * Post item sync event handler.
     *
     * @param OrderItemEvent $event
     */
    public function onPostSync(OrderItemEvent $event)
    {
        $this->dispatcher->dispatch(OrderEvents::CONTENT_CHANGE, $event);
    }

    /**
     * Pre item remove event handler.
     *
     * @param OrderItemEvent $event
     * @throws LogicException
     */
    public function onPreRemove(OrderItemEvent $event)
    {
        if ($this->isOrderLocked($event)) {
            return;
        }

        $this->prepareEvent($event);
    }

    /**
     * Item remove event handler.
     *
     * @param OrderItemEvent $event
     */
    public function onRemove(OrderItemEvent $event)
    {
        $order = $event->getOrder();
        $item  = $event->getItem();

        $order->removeItem($item);
    }

    /**
     * Post item remove event handler.
     *
     * @param OrderItemEvent $event
     */
    public function onPostRemove(OrderItemEvent $event)
    {
        $this->dispatcher->dispatch(OrderEvents::CONTENT_CHANGE, $event);
    }

    /**
     * Prepares the event by transforming the subject if needed.
     *
     * @param OrderItemEvent $event
     * @throws LogicException
     */
    private function prepareEvent(OrderItemEvent $event)
    {
        if (!$event->hasItem()) {
            if (!$event->hasSubject()) {
                throw new LogicException('At least the item or the subject must be set.');
            }
            $event->setItem($this->helper->transform($event->getSubject()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
    	return array(
    	    OrderItemEvents::ADD => array(
    	        array('onPreAdd',      1024),
        	    array('onAdd',           0),
        	    array('onPostAdd',    -1024),
    	    ),
    	    OrderItemEvents::SYNC => array(
    	        array('onPreSync',      1024),
        	    array('onPostSync',    -1024),
    	    ),
            OrderItemEvents::REMOVE => array(
    	        array('onPreRemove',   1024),
    	        array('onRemove',        0),
    	        array('onPostRemove', -1024),
    	    ),
    	);
    }
}
