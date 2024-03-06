<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Override;

class ElasticsearchRepository implements ElasticsearchRepositoryInterface
{
    private readonly Client $client;

    private readonly string $index;

    public function __construct(string $host, int $port, string $index)
    {
        $this->index = strtolower($index);

        $this->client = ClientBuilder::create()->setHosts([$host . ':' . $port])->build();
        $params = ['index' => $this->index];
        try {
            $response = $this->client->indices()->getSettings($params);
        } catch (Missing404Exception) {
            $response = $this->client->indices()->create($params);
        }
    }

    #[Override]
    public function clear(): void
    {
        $params = ['index' => $this->index];
        $response = $this->client->indices()->delete($params);
        $response = $this->client->indices()->create($params);
    }

    #[Override]
    public function search(string $searchString): array
    {
        $params = [
            'index' => $this->index,
            '_source_includes' => ['title']
        ];

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = strtolower($searchStringPart) . '*';
        }

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits === 0) {
            return [];
        }

        $searchResults = [];
        foreach ($result['hits']['hits'] as $hit) {
            $searchResult = new SearchResultDocument(
                path: FilePath::parse($hit['_id']),
                title: Asserted::notNull($hit['_source']['title'] ?? null, 'No title set'),
                score: Asserted::float($hit['_score'])
            );

            $searchResults[] = $searchResult;
        }

        return $searchResults;
    }

    #[Override]
    public function indexFile(FilePath $path, AnalyzedDocument $document): mixed
    {
        $params = [
            'id' => $path->toAbsoluteString(),
            'index' => $this->index,
            'body' => [
                'title' => $document->title,
                'content' => $document->analyzedContent,
                'linked_paths' => $document->getLinkedPaths(),
                'extension' => $path->getExtension(),
            ]
        ];

        return $this->client->index($params);
    }

    #[Override]
    public function deleteFile(FilePath $path): mixed
    {
        $params = [
            'id' => $path->toAbsoluteString(),
            'index' => $this->index,
        ];

        return $this->client->delete($params);
    }

    #[Override]
    public function findTitle(FilePath $path): ?string
    {
        try {
            $params = [
                'id' => $path->toAbsoluteString(),
                'index' => $this->index,
                '_source_includes' => ['title']
            ];
            $result = $this->client->get($params);
        } catch (Missing404Exception) {
            return null;
        }

        return $result['_source']['title'];
    }
}
