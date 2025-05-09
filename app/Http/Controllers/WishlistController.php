<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToWishlistRequest;
use App\Http\Resources\ProductResource;
use Auth;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{

    /**
     * Display a listing of the products in the user's wishlist.
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch the products in the user's wishlist
        $wishlistItems = $user->wishedProducts()->get();

        return ProductResource::collection($wishlistItems);
    }

    /**
     * Add a product to the user's wishlist.
     * @param AddToWishlistRequest $request
     * @return JsonResponse
     */
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

    /**
     * Remove a product from the user's wishlist.
     * @param int $productId
     * @return JsonResponse
     */
    public function destroy(int $productId)
    {
        $user = Auth::user();

        if (!$user->wishedProducts()->where('product_id', $productId)->exists()) {
            return response()->json(['message' => 'Product not found in wishlist.'], 404);
        }

        $user->wishedProducts()->detach($productId);

        return response()->json(['message' => 'Product removed from wishlist successfully.']);
    }
}
