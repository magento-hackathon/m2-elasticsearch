<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */

namespace MagentoHackathon\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MagentoHackathon\Elasticsearch\Model\Adapter\AdapterInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * @category   Model
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Indexer
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Vadim Justus <v.justus@techdivision.com>
 */

class IndexerHandler implements IndexerInterface
{
    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param AdapterInterface $adapter
     * @param ScopeConfigInterface $scopeConfig
     * @param Batch $batch
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        AdapterInterface $adapter,
        ScopeConfigInterface $scopeConfig,
        Batch $batch,
        array $data = [],
        $batchSize = 500
    ) {
        $this->adapter = $adapter;
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
        $this->batch = $batch;
        $this->batchSize = $batchSize;
    }

    /**
     * @return self
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $bulkData = $this->adapter->prepareDataForBulkUpdate($documentsBatch, $dimensions);
            $this->adapter->addDataBulk($bulkData);
        }
        return $this;
    }

    /**
     * @return self
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $bulkData = $this->adapter->prepareDataForBulkDelete($documents, $dimensions);
        $this->adapter->deleteDataBulk($bulkData);
        return $this;
    }

    /**
     * @return self
     */
    public function cleanIndex($dimensions)
    {
        $this->adapter->deleteData($dimensions);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->adapter->ping();
    }
}
