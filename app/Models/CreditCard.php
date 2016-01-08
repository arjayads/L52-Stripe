<?php
/**
 * Created by PhpStorm.
 * User: gwdev1
 * Date: 1/9/16
 * Time: 1:31 AM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model{
    protected $fillable = [
        'token',
        'user_id'
    ];
}