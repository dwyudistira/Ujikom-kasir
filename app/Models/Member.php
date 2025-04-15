<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = "members";

    protected $fillable = [
        'name',
        'member_code',
        'email',
        'phone_number',
        'join_in',
        'points',
    ];
}
