<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = ['user_id', 'level', 'points'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}