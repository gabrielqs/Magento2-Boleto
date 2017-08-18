<?php

namespace Gabrielqs\Boleto\Model\Boleto;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use Magento\Framework\View\Asset\Repository as AssetRepo;

class ConfigProvider extends CcGenericConfigProvider
{

    /**
     * AssetRepo
     * @var AssetRepo
     */
    protected $_assetRepo = null;

    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $_boletoHelper = null;

    /**
     * ConfigProvider constructor.
     *
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param BoletoHelper $boletoHelper
     * @param AssetRepo $assetRepo
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        BoletoHelper $boletoHelper,
        AssetRepo $assetRepo,
        $methodCodes = []
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_boletoHelper = $boletoHelper;
        $methodCodes[$this->_boletoHelper->getMethodCode()] = $this->_boletoHelper->getMethodCode();
        return parent::__construct($ccConfig, $paymentHelper, $methodCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $method = $this->_boletoHelper->getMethodCode();

        if (!$this->_boletoHelper->getConfigData('active')) {
            return [
                'payment' => [
                    $method => [
                        'active' => false,
                    ]
                ]
            ];
        }

        $checkoutImageAsset = $this->_assetRepo->createAsset('Gabrielqs_Boleto::images/checkout.png');
        return [
            'payment' => [
                $method => [
                    'active'         => true,
                    'checkout_image' => $checkoutImageAsset->getUrl()
                ]
            ]
        ];
    }
}