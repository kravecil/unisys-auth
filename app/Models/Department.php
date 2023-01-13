<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Department extends Model
{
    protected $appends = [
        'number_title',
    ];

    protected $fillable = [
        'number', 'title', 'department_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function leaders() {
        return $this->users()->where('is_leader', true);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function childrenDepartments()
    {
        return $this->hasMany(Department::class)
            ->with('departments');
    }

    protected function numberTitle() : Attribute {
        return new Attribute(
            get: function() {
                return $this->number . ' ' . $this->title;
            }
        );
    }
}
