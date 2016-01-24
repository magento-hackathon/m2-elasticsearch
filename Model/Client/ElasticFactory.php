<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */
namespace MagentoHackathon\Elasticsearch\Model\Client;

/**
 * @category   Client
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Interface
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Harald Deiser <h.deiser@techdivision.com>
 */
class ElasticFactory implements \MagentoHackathon\Elasticsearch\Model\Client\FactoryInterface
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        return $this->_objectManager->create('MagentoHackathon\Elasticsearch\Model\Client\Elastic', ['options' => $options]);
    }
}
