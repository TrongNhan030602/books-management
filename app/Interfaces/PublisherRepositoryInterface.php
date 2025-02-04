<?php

namespace App\Interfaces;

interface PublisherRepositoryInterface
{
    public function findPublisherById(int $id);
    public function createPublisher(array $data);
    public function updatePublisher(int $id, array $data);
    public function deletePublisher(int $id);
    public function getAllPublishers();
    public function searchPublishers(string $query);
}
