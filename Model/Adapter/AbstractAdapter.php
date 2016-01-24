<?php
/**
 * MagentoHackathon_Elasticsearch
 * https://github.com/magento-hackathon/m2-elasticsearch
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license or contact license@magentocommerce.com for a copy.
 */
namespace MagentoHackathon\Elasticsearch\Model\Adapter;

use Magento\Framework\Exception\LocalizedException;
use MagentoHackathon\Elasticsearch\Helper\ClientOptionsInterface;
use MagentoHackathon\Elasticsearch\Model\Client\FactoryInterface;
use MagentoHackathon\Elasticsearch\Model\Adapter\AdapterInterface;
use MagentoHackathon\Elasticsearch\Model\ResourceModel\Index;

/**
 * @category   Adapter
 * @package    MagentoHackathon_Elasticsearch
 * @version    1.0.0
 * @link       https://github.com/magento-hackathon/m2-elasticsearch
 * @TODO how to get Index name?
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Elasticsearch Client instance
     *
     * @var \MagentoHackathon\Elasticsearch\Model\Client\Elastic
     */
    protected $client = null;

    /**
     * @var float|bool
     */
    private $ping;

    /**
     * @var FactoryInterface
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface
     */
    private $clientHelper;

    /**
     * @param FactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientHelper
     * @param array $options
     * @throws LocalizedException
     */
    public function __construct(
        FactoryInterface $clientFactory,
        ClientOptionsInterface $clientHelper,
        $options = []
    ) {
        $this->clientFactory = $clientFactory;
        $this->clientHelper = $clientHelper;

        try {
            $this->connect($options);
        } catch (\Exception $e) {
            //TODO Logging
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Connect to Search Engine Client by specified options.
     * Should initialize _client
     *
     * @param array $options
     * @return \MagentoHackathon\Elasticsearch\Model\Client\Elastic
     */
    protected function connect($options = [])
    {
        try {
            $this->client = $this->clientFactory->create($this->clientHelper->prepareClientOptions($options));
        } catch (\Exception $e) {
            //TODO Logging
            throw new \RuntimeException('Elasticsearch client is missing.');
        }

        return $this->client;
    }

    /**
     * @param \Traversable $documents
     * @param array $dimensions
     * @return array
     */
    public function prepareDataForBulkUpdate(\Traversable $documents, $dimensions, $type = 'product')
    {
        $result = array();
        switch($type){
            case 'product':
                foreach($documents as $doc){
                    //TODO how to get key and value from doc (whats in documents)
                    $result[] = $this->client->getProductUpdateDocument($doc->getKey(), $doc->getValue());
                }
                break;
            case 'category':
                break;
            default:
                //TODO Error 'Undefined Bulk Data
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function addDataBulk(array $data)
    {
        $bulkResult = $this->client->updateDocuments($data);

        return $bulkResult->isOk();
    }

    /**
     * @param \Traversable $documents
     * @param array $dimensions
     * @return array
     */
    public function prepareDataForBulkDelete(\Traversable $documents, $dimensions, $type = 'product')
    {
        $result = array();
        switch($type){
            case 'product':
                foreach($documents as $doc){
                    //TODO how to get key and value from doc (whats in documents)
                    $result[] = $this->client->getProductMarkDeleteDocument($doc->getKey(), $doc->getValue());
                }
                break;
            case 'category':
                break;
            default:
                //TODO Error 'Undefined Bulk Data
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function deleteDataBulk(array $data)
    {
        $bulkResult = $this->client->deleteDocuments($data);

        return $bulkResult->isOk();
    }

    /**
     * @param array $dimensions
     * @return bool
     */
    public function deleteData(array $dimensions)
    {
        $this->client->deleteIndex($this->client->getIndex($dimensions));
    }

    /**
     * @return bool
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }
}
