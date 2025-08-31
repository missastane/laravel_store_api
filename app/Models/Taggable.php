<?php

namespace App\Models;

use App\Models\Content\Faq;
use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use App\Models\Market\Brand;
use App\Models\Market\Category;
use App\Models\Market\Product;
use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Model;

class Taggable extends Model
{
    protected $fillable = ['tag_id', 'taggable_type', 'taggable_id'];

    protected $hidden = ['tag_id', 'taggable_type'];
    protected $appends = ['taggable_type_value'];

    public function taggable()
    {
        return $this->morphTo();
    }
    public function getTaggableTypeValueAttribute()
    {
        switch ($this->taggable_type) {
            case 'App\Models\Market\Product':
                $result = 'محصول';
                break;
            case 'App\Models\Market\Category':
                $result = 'دسته بندی محصول';
                break;
            case 'App\Models\Content\Post':
                $result = 'پست';
                break;
            case 'App\Models\Content\PostCategory':
                $result = 'دسته بندی پست';
                break;
            case 'App\Models\Market\Brand':
                $result = 'برند';
                break;
            case 'App\Models\Content\Faq':
                $result = 'سؤالات متداول';
                break;
            case 'App\Models\Setting\Setting':
                $result = 'تنظیمات';
                break;
            default:
                $result = 'نامشخص';
        }
        return $result;
    }

    // public function getTaggableTypeObjectAttribute()
    // {
    //     return $this->taggable;
    // }
}
