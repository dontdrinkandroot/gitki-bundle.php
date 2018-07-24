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
    public function __construct(string $host, int $port, string $index)
    {
        $this->index = strtolower($index);

        $this->client = ClientBuilder::create()->setHosts([$host . ':' . $port])->build();
        $params = ['index' => $this->index];
        try {
            $response = $this->client->indices()->getSettings($params);
        } catch (Missing404Exception $e) {
            $response = $this->client->indices()->create($params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $params = ['index' => $this->index];
        $response = $this->client->indices()->delete($params);
        $response = $this->client->indices()->create($params);
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        $params = [
            'index'           => $this->index,
            '_source_include' => ['title']
        ];

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = strtolower($searchStringPart) . '*';
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
            if (isset($hit['_source'])) {
                if (isset($hit['_source']['title'])) {
                    $searchResult->setTitle($hit['_source']['title']);
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
            'id'    => $path->toAbsoluteString(),
            'index' => $this->index,
            'type'  => 'gitki_document',
            'body'  => [
                'title'        => $document->getTitle(),
                'content'      => $document->getAnalyzedContent(),
                'linked_paths' => $document->getLinkedPaths(),
                'extension'    => $path->getExtension(),
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
            'type'  => 'gitki_document',
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
                'type'            => 'gitki_document',
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
