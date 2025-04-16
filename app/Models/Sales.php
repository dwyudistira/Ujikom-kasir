<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [
        'invoice_number',
        'name',
        'product_id',
        'member_id',
        'product_data',
        'quantity',
        'subtotal',
        'diskon_member',
        'total_paid',
        'made_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

