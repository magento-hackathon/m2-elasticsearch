<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */
namespace MagentoHackathon\Elasticsearch\Model\Client;
use MagentoHackathon\Elasticsearch\Model\Client\Elastica\ConfigurationInterface;

/**
 * @category   Client
 * @package    MagentoHackathon_Elasticsearch
 * @subpackage Interface
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @author     Harald Deiser <h.deiser@techdivision.com>
 * @author     Lars Roettig <l.roettig@techdivision.com>
 */
class Elastica extends \Elastica\Client
{
    /**
     * The elasticsearch export index
     *
     * @var \Elastica\Index Index object.
     */
    protected $index;

    /**
     * The elasticsearch parent index
     *
     * @var \Elastica\Index Index object.
     */
    protected $parentIndex;

    /**
     * @var \Elastica\Document
     */
    protected $document;

    /**
     * The elasticsearch export index
     *
     * @var \Elastica\Type $type Type object
     */
    protected $type;

    /**
     * @var string
     */
    protected $indexName = '';

    /**
     * @var string
     */
    protected $aliasName = '';

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        parent::__construct($this->configuration->getConfigArray(), $this->configuration->getClientCallback());
    }
    /**
     * Get the current elastic export index
     *
     * @param string $elasticIndexName
     *
     * @return \Elastica\Index
     */
    public function getIndex($elasticIndexName)
    {
        if ($this->index != null) {
            return $this->index;
        }

        $settings = $this->configuration->getSearchSettings();
        $format = $this->configuration->getAdvancedNewIndexDateFormat();
        $date = '';
        if (!empty($format)) {
            $date = date($format);
        }

        $md5 = md5(serialize($settings) . $date);

        $this->aliasName = $elasticIndexName;
        $this->indexName = $elasticIndexName . '_' . $md5;

        $this->index = $this->getParentIndex($this->aliasName);

        if (!$this->index->exists() || $this->index->getName() != $this->indexName) {
            $this->index = $this->getParentIndex($this->indexName);

            if (!$this->index->exists()) {
                $this->index->create($settings, false);
            }
        }

        return $this->index;
    }

    /**
     * Get the currentType
     *
     * @param      $type
     * @param bool $createNew
     *
     * @return \Elastica\Type
     */
    public function getType($type, $createNew = false)
    {
        $this->_type = $this->index->getType($type);

        if ($createNew == true && $this->type->exists() == true) {
            $this->type->delete();
        }

        return $this->type;
    }

    /**
     * Creates a new document
     *
     * @param string $id
     * @param array $data
     * @param string $type
     * @param string $index
     *
     * @return \Elastica\Document
     *
     * @codeCoverageIgnore
     */
    public function createNewDocument($id = '', $data = array(), $type = '', $index = '')
    {
        if (!is_null($this->document)) {
            return $this->document;
        }
        return new \Elastica\Document($id, $data, $type, $index);
    }

    /**
     * This method wrapps the parent method call only.
     * Needed for testing.
     *
     * @param string $elasticIndexName
     *
     * @return \Elastica\Index
     */
    private function getParentIndex($elasticIndexName)
    {
        if (!is_null($this->parentIndex)) {
            return $this->parentIndex;
        }

        // @codeCoverageIgnoreStart
        return parent::getIndex($elasticIndexName);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Build bulk array to update Attributes in Elasticsearch
     *
     * @param array $productIds
     * @param array $productValues
     *
     * @return array
     */
    public function buildAttributeBulkUpdate(array $productIds, array $productValues)
    {
        $bulkDocumentSet = array();

        foreach ($productIds as $productId) {
            array_push($bulkDocumentSet, $this->getProductUpdateDocument($productId, $productValues));
        }

        return $bulkDocumentSet;
    }

    /**
     * Get elastic document with "is_delete" flag is true
     *
     * @param $productId
     *
     * @return \Elastica\Document
     */
    public function getProductMarkDeleteDocument($productId)
    {
        $productValue = array("is_delete" => true);
        $doc = $this->createNewDocument($productId, $productValue);
        $doc->setDocAsUpsert(true);
        return $doc;
    }

    /**
     * Get elastic document with "is_delete" flag is true
     *
     * @param       $productId
     * @param array $productValue
     *
     * @return \Elastica\Document
     */
    public function getProductUpdateDocument($productId, array $productValue)
    {
        $doc = $this->createNewDocument($productId, $productValue);
        $doc->setDocAsUpsert(true);
        return $doc;
    }

    /**
     * Build bulk array to update Products in Elasticsearch
     *
     * @param array $entities
     * @param array $entityIds
     * @param string $idField
     *
     * @return array
     */
    public function buildBulkUpdate(array &$entities, array &$entityIds, $idField = 'entity_id')
    {
        $bulkDocumentSet = array();

        // build bulk update for request performance
        foreach ($entities as $entityKey => $entityValue) {

            // if you delete some product i must find that and add this to deleteArray for clean elasticsearch
            if (($key = array_search($entityValue[$idField], $entityIds)) !== false) {
                unset($entityIds[$key]);
            }

            $doc = $this->createNewDocument($entityValue[$idField], $entityValue);
            $doc->setDocAsUpsert(true);

            array_push($bulkDocumentSet, $doc);
            // prevent for memory leak
            unset($entities[$entityKey]);
        }
        return $bulkDocumentSet;
    }

    /**
     * remove the eav deleted products from elasticsearch index
     *
     * @param array $productIds
     */
    public function cleanElasticSearchIndex(array $productIds)
    {
        foreach ($productIds as $productId) {
            try {
                $this->type->deleteById($productId);
            } catch (\Exception $e) {
                //continue if $productId not found
                //TODO Logging
                continue;
            }
        }
    }

    /**
     * @param string $indexName
     *
     * @return \Elastica\Response
     */
    public function deleteIndex($indexName)
    {
        $index = $this->getParentIndex($indexName);
        return $index->delete();
    }

    /**
     * @param string $indexName
     * @param string $aliasName
     *
     * @return \Elastica\Response
     */
    public function changeAlias($indexName, $aliasName)
    {
        $index = $this->getParentIndex($indexName);

        try {
            $aliasIndex = $this->getParentIndex($aliasName);
            $aliasIndex->removeAlias($aliasName);
        } catch (\RuntimeException $exception) {
            //TODO Logging
        }

        return $index->addAlias($aliasName);
    }

    /**
     * @return void
     */
    public function updateAlias()
    {
      //TODO Change Alias
    }
}
