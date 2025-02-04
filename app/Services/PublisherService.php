<?php

namespace App\Services;

use App\Interfaces\PublisherRepositoryInterface;

class PublisherService
{
    protected $publisherRepository;

    public function __construct(PublisherRepositoryInterface $publisherRepository)
    {
        $this->publisherRepository = $publisherRepository;
    }

    public function findPublisherById(int $id)
    {
        return $this->publisherRepository->findPublisherById($id);
    }

    public function createPublisher(array $data)
    {
        return $this->publisherRepository->createPublisher($data);
    }

    public function updatePublisher(int $id, array $data)
    {
        return $this->publisherRepository->updatePublisher($id, $data);
    }

    public function deletePublisher(int $id)
    {
        return $this->publisherRepository->deletePublisher($id);
    }

    public function getAllPublishers()
    {
        return $this->publisherRepository->getAllPublishers();
    }
    public function searchPublishers(string $query)
    {
        return $this->publisherRepository->searchPublishers($query);
    }
}
