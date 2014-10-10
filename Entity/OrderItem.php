<?php

namespace Ekyna\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Sale\PriceableTrait;
use Ekyna\Component\Sale\Product\ProductInterface;
use Ekyna\Component\Sale\Order\OrderInterface;
use Ekyna\Component\Sale\Order\OrderItemInterface;
use Ekyna\Component\Sale\Order\OrderItemOptionInterface;
use Ekyna\Component\Sale\ReferenceableTrait;
use Ekyna\Component\Sale\TaxAmount;
use Ekyna\Component\Sale\TaxesAmounts;
use Ekyna\Component\Sale\WeightableTrait;

/**
 * Class OrderItem
 * @package Ekyna\Bundle\OrderBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItem implements OrderItemInterface
{
    use PriceableTrait;
    use ReferenceableTrait;
    use WeightableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $quantity;

    /**
     * @var integer
     */
    protected $position;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var ArrayCollection|OrderItemOptionInterface[]
     */
    protected $options;

    /**
     * @var array
     */
    protected $extras;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->extras = array();
    }

    /**
     * Returns the identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(OrderInterface $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(OrderItemOptionInterface $option)
    {
        $option->setOrderItem($this);
        $this->options->add($option);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(OrderItemOptionInterface $option)
    {
        $option->setOrderItem(null);
        $this->options->removeElement($option);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsIds()
    {
        $ids = array();
        foreach($this->options as $option) {
            $ids[] = $option->getOption()->getId();
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(OrderItemInterface $orderItem)
    {
        if ($this->hasSameProduct($orderItem)) {
            return $this->hasSameOptions($orderItem);
        }
        return false;
    }

    /**
     * Returns whether the OrderItem has the same product as the given OrderItem or not.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return boolean
     */
    protected function hasSameProduct(OrderItemInterface $orderItem)
    {
        return $this->product->getId() === $orderItem->getProduct()->getId();
    }

    /**
     * Returns whether the OrderItem has the same options as the given OrderItem or not.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return boolean
     */
    protected function hasSameOptions(OrderItemInterface $orderItem)
    {
        $thisOptionsIds = $this->getOptionsIds();
        $itemOptionsIds = $orderItem->getOptionsIds();
        return (count($thisOptionsIds) === count($itemOptionsIds) && count(array_diff($thisOptionsIds, $itemOptionsIds)) === 0);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(OrderItemInterface $orderItem)
    {
        if ($this->equals($orderItem)) {
            $this->quantity += $orderItem->getQuantity();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseNetPrice()
    {
        $netPrice = $this->getNetPrice();
        foreach ($this->options as $option) {
            $netPrice += $option->getNetPrice();
        }
        return $netPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAtiPrice()
    {
        $atiPrice = $this->getAtiPrice();
        foreach ($this->options as $option) {
            $atiPrice += $option->getAtiPrice();
        }
        return $atiPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTaxAmount()
    {
        $taxAmount = $this->getTaxAmount();
        foreach ($this->options as $option) {
            $taxAmount += $option->getTaxAmount();
        }
        return $taxAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseWeight()
    {
        $weight = $this->getWeight();
        foreach ($this->options as $option) {
            $weight += $option->getWeight();
        }
        return $weight;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalNetPrice()
    {
        return $this->getBaseNetPrice() * $this->quantity;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAtiPrice()
    {
        return $this->getBaseAtiPrice() * $this->quantity;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalTaxAmount()
    {
        return $this->getBaseTaxAmount() * $this->quantity;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalTaxesAmounts()
    {
        $amounts = new TaxesAmounts();
        $amounts->addTaxAmount(new TaxAmount($this->tax, $this->getTaxAmount() * $this->quantity));
        foreach ($this->options as $option) {
            $amounts->addTaxAmount(new TaxAmount($option->getTax(), $option->getTaxAmount() * $this->quantity));
        }
        return $amounts;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalWeight()
    {
        return $this->getBaseWeight() * $this->quantity;
    }
}
