<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Return Paginated Products
     * @unauthenticated // this is required by the scramble package to show unauthenticated routes
     */
    public function index()
    {
        return ProductResource::collection(Product::paginate(10));
    }

    /**
     * Return a specific product
     *
     * @param Product $product
     * @return ProductResource
     * @unauthenticated // this is required by the scramble package to show unauthenticated routes
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

}
