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
 * @author     Lars Roettig <l.roettig@techdivision.com>
 */
class Elastic extends \Elastica\Client
{
    /**
     * The elasticsearch export index
     *
     * @var \Elastica\Index Index object.
     */
    protected $_index;

    /**
     * @var \Elastica\Document
     */
    protected $_document;

    /**
     * The elasticsearch export index
     *
     * @var \Elastica\Type $type Type object
     */
    protected $_type;

    /**
     * @var string
     */
    protected $_indexName = '';

    /**
     * @var string
     */
    protected $_aliasName = '';

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
        $this->_type = $this->_index->getType($type);

        if ($createNew == true && $this->_type->exists() == true) {
            $this->_type->delete();
        }

        return $this->_type;
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
        if (!is_null($this->_document)) {
            return $this->_document;
        }
        return new \Elastica\Document($id, $data, $type, $index);
    }

    /**
     * This method wrapps the parent method call only.
     * Needed for testing.
     *
     * @param string $elasticsearchIndex
     *
     * @return \Elastica\Index
     */
    private function _getParentIndex($elasticsearchIndex)
    {
        if (!is_null($this->_parentIndex)) {
            return $this->_parentIndex;
        }

        // @codeCoverageIgnoreStart
        return parent::getIndex($elasticsearchIndex);
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
     * Get elastica document with "is_delete" flag is true
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
     * Get elastica document with "is_delete" flag is true
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
                $this->_type->deleteById($productId);
            } catch (Exception $e) {
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
        $index = $this->_getParentIndex($indexName);
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
        $index = $this->_getParentIndex($indexName);

        try {
            $aliasIndex = $this->_getParentIndex($aliasName);
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
