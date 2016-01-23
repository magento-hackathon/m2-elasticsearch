<?php
/**
 * MagentoHackathon\Elasticsearch\AdapterInterface\Model
 */

namespace MagentoHackathon\Elasticsearch\Model;

/**
 * @category   Interface
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage AdapterInterface
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