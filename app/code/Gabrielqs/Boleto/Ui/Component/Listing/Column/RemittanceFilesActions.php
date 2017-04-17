<?php

namespace Gabrielqs\Boleto\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class RemittanceFilesActions
 */
class RemittanceFilesActions extends Column
{
    /**
     * Url paths
     */
    const URL_PATH_DELETE = 'boleto/remittance/delete';
    const URL_PATH_VIEW = 'boleto/remittance/view';

    /**
     * Url Builder
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['remittance_file_id'])) {
                    $downloadUrlPath = $this->getData('config/downloadUrlPath') ?: '#';
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'remittance_file_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['remittance_file_id']
                                ]
                            ),
                            'label' => __('View')
                        ],
                        'download' => [
                        'href' => $this->urlBuilder->getUrl(
                            $downloadUrlPath,
                            [
                                $urlEntityParamName => $item['remittance_file_id']
                            ]
                        ),
                        'label' => __('Download')
                    ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
