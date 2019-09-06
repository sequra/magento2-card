<?php
/**
 * Copyright © 2017 SeQura Engineering. All rights reserved.
 */

namespace Sequra\Card\Model\Service;

use Sequra\Card\Api\CardQueryInterface;

/**
 * Class CardQuery
 *
 */
class CardQuery implements CardQueryInterface
{
    /**
     * @var \Sequra\PhpClient\Client
     */
    protected $client;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Sequra\Card\Model\Api\BuilderFactory
     */
    protected $builderFactory;

    /**
     * Card store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Sequra\Core\Model\Api\BuilderFactory $builderFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->builderFactory = $builderFactory;
        $this->context = $context;
    }

    public function startQuery()
    {
        $builder = $this->builderFactory->create('order');
        $data = $builder->setOrder(
            $this->checkoutSession->getQuote()
        )->build();
        $this->getClient()->startCards($data);
        $this->checkoutSession->setCardQueryUri(
            $this->getClient()->getOrderUri()
        );
        if (!$this->getClient()->succeeded()) {
            http_response_code($client->getStatus());
            die();
        }
    }

    public function getQueryResult()
    {
        $uri = $this->checkoutSession->getCardQueryUri();
        $this->getClient()->getCardsForm($uri);

        if ($this->getClient()->succeeded()) {
            $response['result'] = 'done';
            $response['card'] = $this->getClient()->getJson();
        } else {
            $response['result'] = 'retry';
        }
        return json_encode($response);
    }

    private function getClient()
    {
        if (!$this->client) {
            $this->client = new \Sequra\PhpClient\Client(
                $this->getConfigData('user_name'),
                $this->getConfigData('user_secret'),
                $this->getConfigData('endpoint')
            );
        }
        return $this->client;
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
