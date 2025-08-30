<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;


class ProductImportController extends Controller
{
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,xlsx,txt'
    ]);

    Excel::import(new ProductsImport, $request->file('file'));

    return response()->json(['message' => 'Products imported successfully!']);
}

}
