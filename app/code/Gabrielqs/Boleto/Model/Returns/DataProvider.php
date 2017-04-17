<?php

namespace Gabrielqs\Boleto\Model\Returns;

use \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use \Magento\Ui\DataProvider\AbstractDataProvider;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Collection as ReturnsFileCollection;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\CollectionFactory as ReturnsFileCollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * Returns File Collection
     * @var ReturnsFileCollection
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
     * @param ReturnsFileCollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReturnsFileCollectionFactory $collectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->filterPool = $filterPool;
    }
}