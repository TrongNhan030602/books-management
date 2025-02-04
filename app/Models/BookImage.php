<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookImage extends Model
{
    protected $fillable = ['book_id', 'image_url'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}