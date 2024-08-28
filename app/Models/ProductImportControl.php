<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ProductImportControl extends Model
{

	protected $connection = 'mongodb';

	protected $fillable = ['file_name', 'imported_t', 'created_t', 'updated_t'];

}
