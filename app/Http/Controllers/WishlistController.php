<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToWishlistRequest;
use App\Http\Resources\ProductResource;
use Auth;

class WishlistController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        // Fetch the products in the user's wishlist
        $wishlistItems = $user->wishedProducts()->get();

        return ProductResource::collection($wishlistItems);
    }

    public function store(AddToWishlistRequest $request)
    {
        // All the validation is done in the form request class
        $user = Auth::user();

        if ($user->wishedProducts()->where('product_id', $request->product_id)->exists()) {
            return response()->json(['message' => 'Product already in wishlist.'], 409);
        }

        $user->wishedProducts()->attach($request->product_id);

        return response()->json([
            'message' => 'Product added to wishlist successfully.',
        ], 201);
    }

    public function destroy($productId)
    {
        $user = Auth::user();

        if (!$user->wishedProducts()->where('product_id', $productId)->exists()) {
            return response()->json(['message' => 'Product not found in wishlist.'], 404);
        }

        $user->wishedProducts()->detach($productId);

        return response()->json(['message' => 'Product removed from wishlist successfully.']);
    }
}
