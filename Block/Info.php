<?php
/**
 * Copyright © 2019 Tiralineas Estudio. All rights reserved.
 */

namespace Sequra\Card\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Sequra\Core\Model\Api\BuilderFactory
     */
    protected $builderFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CheckoutSession
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Sequra\Core\Model\Api\BuilderFactory $builderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->scopeConfig = $scopeConfig;
        $this->builderFactory = $builderFactory;
        $this->checkoutSession = $checkoutSession;
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $builder = $this->_builderFactory->create('order');
        $data = $builder->setOrder($quote)->build();
        $client = new \Sequra\PhpClient\Client(
            $this->getConfigData('user_name'),
            $this->getConfigData('user_secret'),
            $this->getConfigData('endpoint')
        );
        $client->startCard($data);
        $this->checkoutSession->setSequraCardUri($client->getOrderUri());

        return $transport;
    }

    private function getConfigData($field, $storeId = null)
    {
        $path = 'sequra/core/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function getPaymentConfigData($payment_code, $field, $storeId = null)
    {
        $path = 'payment/' . $payment_code . '/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
