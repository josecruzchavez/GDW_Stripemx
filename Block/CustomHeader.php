<?php
namespace GDW\Stripemx\Block;
use GDW\Stripemx\Model\StripemxCard;
use Magento\Framework\View\Element\Template\Context;

class CustomHeader extends \Magento\Framework\View\Element\Template
{
    protected $_stripemx;
    
    public function __construct(StripemxCard $_stripemx, Context $context)
    {
      $this->_stripemx = $_stripemx;
      parent::__construct($context);
    }

    public function enableStripemx()
    {
      return $this->_stripemx->enable();
    }

    public function getCustomScript()
    {
      return $this->_stripemx->getStripeScript();
    }
}