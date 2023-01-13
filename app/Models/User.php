<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'username',
        'password',
        'is_leader',
        'department_id',
        'lastname',
        'firstname',
        'middlename',
        'post',
    ];

    protected $appends = ['fullname', 'shortname'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions() {
        $permissions = collect();

        foreach($this->roles as $role) {
            $permissions = $permissions->concat($role->permissions);
        }

        return $permissions;
    }

    public function desktops() {
        $desktops = collect();

        if (!!$this->roles()->find(1)) $desktops = Desktop::all();

        else foreach($this->roles as $role) {
            $desktops = $desktops->concat($role->desktops);
        }

        return $desktops;
    }

    public function fullname() : Attribute {
        return new Attribute(
            get: function($value) {
                return $this->lastname . ' ' .$this->firstname . ' ' . $this->middlename;
            }
        );
    }

    public function shortname() : Attribute {
        return new Attribute(
            get: function($value) {
                return $this->lastname . ' ' .
                    Str::substr(Str::ucfirst($this->firstname),0,1) . '.' .
                    Str::substr(Str::ucfirst($this->middlename),0,1) . '.';
            }
        );
    }

    public function hasPermission($permissions) {
        foreach ($this->roles as $role) {
            if (
                collect($permissions)
                    ->some(function ($value, $key) use ($role) {
                        if ($role->permissions->some('name', $value) || $role->permissions->some('name', 'administration')) {
                            return true;
                        };
                    })
            ) return true;
        }

        return false;
    }

    public function permissionsDetailed()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function can($abilities, $arguments = [])
    {
        $currentUserPermissions = $this->permissions()->pluck('name');
        if ($currentUserPermissions->contains('administration')) return true;
        $abilities = collect($abilities);
        if (!$currentUserPermissions->contains(function ($value, $key) use ($abilities) {
            return $abilities->contains($value);
        })) abort(403, __('auth.not_authorized'));

        return true;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
