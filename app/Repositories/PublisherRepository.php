<?php

namespace App\Repositories;

use App\Models\Publisher;
use App\Interfaces\PublisherRepositoryInterface;

class PublisherRepository implements PublisherRepositoryInterface
{
    public function findPublisherById(int $id)
    {
        return Publisher::find($id);
    }

    public function createPublisher(array $data)
    {
        return Publisher::create($data);
    }

    public function updatePublisher(int $id, array $data)
    {
        $publisher = Publisher::find($id);
        if ($publisher) {
            $publisher->update($data);
        }
        return $publisher;
    }

    public function deletePublisher(int $id)
    {
        return Publisher::destroy($id);
    }

    public function getAllPublishers()
    {
        return Publisher::all();
    }
    public function searchPublishers(string $query)
    {
        return Publisher::where('name', 'like', '%' . $query . '%')
            ->orWhere('address', 'like', '%' . $query . '%')
            ->get();
    }
}