<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desktop extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'path', 'under_construction'];

    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}
