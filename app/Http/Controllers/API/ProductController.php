<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $desc = $request->input('desc');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);
            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Data product berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data product tidak ada',
                    404
                );
            }
        }

        $product = Product::with(['category', 'galleries']);

        if ($name) {
            $product->where('name', 'LIKE', '%' . $name . '%');
        }
        if ($desc) {
            $product->where('descriptions', 'LIKE', '%' . $desc . '%');
        }
        if ($tags) {
            $product->where('tags', 'LIKE', '%' . $tags . '%');
        }
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }
        if ($categories) {
            $product->where('categories', $categories);
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data product berhasil diambil'
        );
    }
}
