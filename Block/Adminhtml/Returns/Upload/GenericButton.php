<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\Upload;

use \Magento\Backend\Block\Widget\Context;
use \Magento\Framework\Registry;
use \Gabrielqs\Boleto\Api\ReturnsFileRepositoryInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;


/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * Context
     * @var Context
     */
    protected $context;

    /**
     * Returns File Repository Interface
     * @var ReturnsFileRepositoryInterface
     */
    protected $returnsFileRepositoryInterface;

    /**
     * Constructor
     * @param Context $context
     * @param ReturnsFileRepositoryInterface $returnsFileRepositoryInterface
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        ReturnsFileRepositoryInterface $returnsFileRepositoryInterface,
        Registry $coreRegistry
    ) {
        $this->context = $context;
        $this->returnsFileRepositoryInterface = $returnsFileRepositoryInterface;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Return Returns File ID
     * @return int|null
     */
    public function getReturnsFileId()
    {
        return (int) $this->_coreRegistry->registry(RegistryConstants::CURRENT_RETURNS_FILE_ID);
    }

    /**
     * Generate url by route and parameters
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
