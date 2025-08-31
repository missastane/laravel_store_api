<?php
namespace App\Http\Services\Compare;

class CompareService
{
       /**
     * to send error messages
     */
    public function errorResponse($message, $data = [])
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], 400);
    }

    /**
     * to receive products attributes
     */
    public function getProductAttributes($products)
    {
        $attributes = [];

        foreach ($products as $product) {
            foreach ($product->category->attributes as $attribute) {
                $values = $attribute->values
                    ->where('product_id', $product->id)
                    ->pluck('value')
                    ->map(fn($value) => $value['value'])
                    ->toArray();

                $attributes[$attribute->name][$product->name] = implode(', ', $values);
            }
        }

        return $attributes;
    }

    /**
     * format products to send to front
     */
    public function formatProductDetails($products)
    {
        return $products->map(function ($product) {
            return [
                'name' => $product->name,
                'image' => $product->image['indexArray']['medium'],
                'price' => $product->price,
                'width' => $product->width,
                'height' => $product->height,
                'length' => $product->length,
                'weight' => $product->weight,
            ];
        });
    }
}