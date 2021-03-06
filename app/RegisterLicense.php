<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegisterLicense extends Model
{
    protected $primaryKey = "id";
     protected $table = 'tbl_register_license';
     public    $timestamps = false;


     public function getClientInfo(){
     	return $this->hasMany("App\ClientInfo","client_id" ,"client_id");
     }

     public function getTransaction(){
     	return $this->hasOne("App\TransactionType","transaction_type_id","transaction_type_id");
     }
}
