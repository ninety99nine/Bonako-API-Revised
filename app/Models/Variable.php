<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variable extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'value', 'product_id'];

    /**
     *  Returns the product that this variable is applied
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
