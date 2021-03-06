<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_client_info';
    protected $primaryKey = 'client_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name','last_name', 'email', 'password','address','mobile','birth','gender','age','username','password','confirmPassword'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public function getCounter(){
        return $this->hasOne("App\Counter", "counter_id", "counter_name" );
    }

    public function getRegisterLicense(){
        return $this->hasMany("App\RegisterLicense","rl_id","rl_id");
    }

    public function getRegisterVehicle(){
        return $this->hasMany("App\RegisterVehicle","rv_id","rv_id");
    }
}
