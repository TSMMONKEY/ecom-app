<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';
    protected $fillable = [
        'name',
        'paragraph',
        'price',
        'stripe_product_id', // Add this line
    ]; // Specify your actual table name if not 'products'
}
