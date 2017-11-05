<?php


namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ElasticsearchRepository implements ElasticsearchRepositoryInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $index;

    /**
     * @param string $host
     * @param int    $port
     * @param string $index
     */
    public function __construct($host, $port, $index)
    {
        $this->index = strtolower($index);

        $params = [];
        $params['hosts'] = [];

        $this->client = ClientBuilder::create()->setHosts([$host . ':' . $port])->build();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $params = [
            'index'  => $this->index,
        ];

        $params['body']['query']['match_all'] = [
            'boost' => 1.0,
        ];
        $params['body']['size'] = 10000;

        try {
            $result = $this->client->search($params);
        } catch (Missing404Exception $e) {
            /* If index is missing nothing to be done */
            return;
        }

        foreach ($result['hits']['hits'] as $hit) {
            $params = [
                'id'   => $hit['_id'],
                'index' => $this->index,
                'type' => $hit['_type']
            ];

            $this->client->delete($params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        $params = [
            'index'  => $this->index,
            'fields' => ['title']
        ];

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = $searchStringPart . '*';
        }

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits == 0) {
            return [];
        }

        $searchResults = [];
        foreach ($result['hits']['hits'] as $hit) {
            $searchResult = new SearchResultDocument(FilePath::parse($hit['_id']));
            $searchResult->setScore($hit['_score']);
            if (isset($hit['fields'])) {
                if (isset($hit['fields']['title'][0])) {
                    $searchResult->setTitle($hit['fields']['title'][0]);
                }
            }
            $searchResults[] = $searchResult;
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $path, AnalyzedDocument $document)
    {
        $params = [
            'id'   => $path->toAbsoluteString(),
            'index' => $this->index,
            'type' => $path->getExtension(),
            'body' => [
                'title'        => $document->getTitle(),
                'content'      => $document->getAnalyzedContent(),
                'linked_paths' => $document->getLinkedPaths()
            ]
        ];

        return $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path)
    {
        $params = [
            'id'    => $path->toAbsoluteString(),
            'index' => $this->index,
            'type'  => $path->getExtension()
        ];

        return $this->client->delete($params);
    }

    /**
     * {@inheritdoc}
     */
    public function findTitle(FilePath $path)
    {
        try {
            $params = [
                'id'              => $path->toAbsoluteString(),
                'index'           => $this->index,
                'type'            => $path->getExtension(),
                '_source_include' => ['title']
            ];
            $result = $this->client->get($params);
            if (null === $result) {
                return null;
            }
        } catch (Missing404Exception $e) {
            return null;
        }

        return $result['_source']['title'];
    }
}
