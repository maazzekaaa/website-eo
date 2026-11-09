<?php

namespace App\Http\Controllers;

use App\Services\CartPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __invoke(Request $request, CartPricingService $pricing): JsonResponse
    {
        $cart = $pricing->price((array) $request->input('items', []), (array) $request->input('qty', []));

        return response()->json([
            'lines' => $cart->lines, 'subtotal' => $cart->subtotal, 'tax' => $cart->tax,
            'total' => $cart->total, 'dp' => $cart->dp,
        ]);
    }
}
