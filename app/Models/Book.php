<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    protected $table = 'books';
    protected $fillable = [
        'title',
        'cover_image',
        'author',
        'publisher_id',
        'price',
        'initial_quantity',
        'quantity',
        'published_year',
        'description',
        'status',
        'location',
        'category_id'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function borrowTransactions()
    {
        return $this->hasMany(related: BorrowTransaction::class);
    }
    public function images()
    {
        return $this->hasMany(BookImage::class);
    }

    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }


    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}