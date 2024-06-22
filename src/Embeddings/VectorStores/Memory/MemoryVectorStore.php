<?php

namespace LLPhant\Embeddings\VectorStores\Memory;

use Exception;
use LLPhant\Embeddings\Distances\Distance;
use LLPhant\Embeddings\Distances\EuclideanDistanceL2;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

class MemoryVectorStore extends VectorStoreBase
{
    /** @var Document[] */
    private array $documentsPool = [];

    public function __construct(private readonly Distance $distance = new EuclideanDistanceL2())
    {
    }

    public function addDocument(Document $document): void
    {
        $this->documentsPool[] = $document;
    }

    public function addDocuments(array $documents): void
    {
        $this->documentsPool = array_merge($this->documentsPool, $documents);
    }

    /**
     * @throws Exception
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $distances = [];

        foreach ($this->documentsPool as $index => $document) {
            if ($document->embedding === null) {
                throw new Exception("Document with the following content has no embedding: {$document->content}");
            }
            $dist = $this->distance->measure($embedding, $document->embedding);
            $distances[$index] = $dist;
        }

        asort($distances); // Sort by distance (ascending).

        $topKIndices = array_slice(array_keys($distances), 0, $k, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = $this->documentsPool[$index];
        }

        return $results;
    }
}
