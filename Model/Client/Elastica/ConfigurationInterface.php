<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */

namespace MagentoHackathon\Elasticsearch\Model\Client\Elastica;
/**
 * @category   Model
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Client/Elastica
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Harald Deiser <h.deiser@techdivision.com>
 * @author     Vadim Justus <v.justus@techdivision.com>
 */

interface ConfigurationInterface
{
    /**
     * @return mixed
     */
    public function getSearchSettings();

    /**
     * @return mixed
     */
    public function getAdvancedNewIndexDateFormat();

    /**
     * @return array
     */
    public function getConfigArray();

    /**
     * @return callback|callable
     */
    public function getClientCallback();
}