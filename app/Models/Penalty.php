<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $table = 'penalties';

    protected $fillable = ['user_id', 'borrow_transaction_id', 'amount', 'reason', 'due_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function borrowTransaction()
    {
        return $this->belongsTo(related: BorrowTransaction::class, foreignKey: 'borrow_transaction_id');
    }
}