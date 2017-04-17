<?php

namespace Gabrielqs\Boleto\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ReturnsFilesActions
 */
class ReturnsFilesActions extends Column
{
    /**
     * Url paths
     */
    const URL_PATH_DELETE = 'boleto/returns/delete';
    const URL_PATH_VIEW = 'boleto/returns/view';

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
                if (isset($item['returns_file_id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $downloadUrlPath = $this->getData('config/downloadUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'returns_file_id';
                    $item[$this->getData('name')] = [
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'returns_file_id' => $item['returns_file_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete return file'),
                                'message' => __('Are you sure you wan\'t to delete a return file record?')
                            ]
                        ],
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['returns_file_id']
                                ]
                            ),
                            'label' => __('View')
                        ],
                        'download' => [
                            'href' => $this->urlBuilder->getUrl(
                                $downloadUrlPath,
                                [
                                    $urlEntityParamName => $item['returns_file_id']
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
