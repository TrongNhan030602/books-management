<?php

namespace App\Repositories;

use App\Models\Review;
use App\Interfaces\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function getAllReviews()
    {
        return Review::all();
    }

    public function findReviewById($id)
    {
        return Review::find($id);
    }


    public function createReview(array $data)
    {
        return Review::create(attributes: $data);
    }

    public function updateReview($id, array $data)
    {
        $review = Review::find(id: $id);
        if ($review) {
            $review->update($data);
            return $review;
        }
        return null;
    }

    public function deleteReview($id)
    {
        $review = Review::find(id: $id);
        if ($review) {
            $review->delete();
            return true;
        }
        return false;
    }

    public function getReviewsByBookId($bookId)
    {
        return Review::where(column: 'book_id', operator: $bookId)->get();
    }

    public function getReviewsByUserId($userId)
    {
        return Review::with(relations: 'book')
            ->where(column: 'user_id', operator: $userId)
            ->paginate(perPage: 10);
    }
}
