<?php

namespace Gabrielqs\Boleto\Model\Remittance;

use \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use \Magento\Ui\DataProvider\AbstractDataProvider;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Collection as RemittanceFileCollection;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\CollectionFactory as RemittanceFileCollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * Remittance File Collection
     * @var RemittanceFileCollection
     */
    protected $collection;

    /**
     * Filter Pool
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * Data Provider Constructor
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RemittanceFileCollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RemittanceFileCollectionFactory $collectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->filterPool = $filterPool;
    }
}