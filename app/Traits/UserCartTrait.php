<?php

namespace App\Traits;

use App\Models\Market\CartItem;
use App\Models\User;

trait UserCartTrait
{
    protected ?User $user = null;
    protected $cartItems = null;

    public function getAuthUser()
    {
        return $this->user ??= auth()->user();
    } 

    public function getCartItems()
    {
        if($this->cartItems === null && $this->getAuthUser())
        {
            $this->cartItems = CartItem::where('user_id',$this->getAuthUser()->id)->with('product:id,name,image,slug','color:id,color_name','guarantee:id,name')->simplePaginate(15);
            $this->cartItems->getCollection()->each(function ($item) {
                $item->product->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
                if ($item->color) {
                    $item->color->makeHidden('status_value');
                }
                if ($item->guarantee) {
                    $item->guarantee->makeHidden('status_value');
                }
            });
        }
        return $this->cartItems;
    }
}
