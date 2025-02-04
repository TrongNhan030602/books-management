<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowTransaction extends Model
{
    protected $table = 'borrow_transactions';
    protected $fillable = ['user_id', 'book_id', 'borrow_date', 'return_date', 'status', 'extension_count', 'reason'];


    public function user()
    {
        return $this->belongsTo(related: User::class);
    }

    public function book()
    {
        return $this->belongsTo(related: Book::class);
    }

    public function penalties()
    {
        return $this->hasMany(related: Penalty::class);
    }
}