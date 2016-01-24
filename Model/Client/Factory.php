<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */

namespace MagentoHackathon\Elasticsearch\Model\Client;
use Magento\Framework\ObjectManagerInterface;

/**
 * @category   Model
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Client
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Harald Deiser <h.deiser@techdivision.com>
 * @author     Vadim Justus <v.justus@techdivision.com>
 */
class Factory implements FactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $clientClass = '';

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $clientClass
    ) {
        $this->objectManager = $objectManager;
        $this->clientClass = $clientClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        return $this->objectManager->create($this->clientClass, ['options' => $options]);
    }
}
