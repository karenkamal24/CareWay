<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'category_id'       => $row['category_id'],
            'name'              => $row['name'],
            'image'             => $row['image'],
            'description'       => $row['description'],
            'price'             => $row['price'],
            'quantity'          => $row['quantity'],
            'status'            => $row['status'],
            'active_ingredient' => $row['active_ingredient'],
        ]);
    }
}
