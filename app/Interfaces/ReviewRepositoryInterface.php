<?php

namespace App\Interfaces;

interface ReviewRepositoryInterface
{
    public function getAllReviews();
    public function findReviewById($id);
    public function createReview(array $data);
    public function updateReview($id, array $data);
    public function deleteReview($id);
    public function getReviewsByBookId($bookId);
    public function getReviewsByUserId($userId);
}