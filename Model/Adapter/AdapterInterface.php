<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */

namespace MagentoHackathon\Elasticsearch\Model\Adapter;

/**
 * @category   Adapter
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Interface
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Vadim Justus <v.justus@techdivision.com>
 */
interface AdapterInterface
{
    /**
     * @param \Traversable $documents
     * @param array $dimensions
     * @return array
     */
    public function prepareDataForBulkUpdate(\Traversable $documents, $dimensions);

    /**
     * @param array $data
     * @return bool
     */
    public function addDataBulk(array $data);

    /**
     * @param \Traversable $documents
     * @param array $dimensions
     * @return array
     */
    public function prepareDataForBulkDelete(\Traversable $documents, $dimensions);

    /**
     * @param array $data
     * @return bool
     */
    public function deleteDataBulk(array $data);

    /**
     * @param array $dimensions
     * @return bool
     */
    public function deleteData(array $dimensions);

    /**
     * @return bool
     */
    public function ping();
}