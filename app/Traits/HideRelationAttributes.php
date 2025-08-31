<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HideRelationAttributes
{
    public function hideRelationAttributes(array $hiddenFieldsByRelation)
    {
        foreach($hiddenFieldsByRelation as $relation => $hiddenFeilds)
        {
            if($this->{$relation} instanceof Model)
            {
                $this->{$relation}->makeHidden($hiddenFeilds);
            }elseif($this->{$relation} instanceof \Illuminate\Support\Collection){
                $this->{$relation}->each->makeHidden($hiddenFeilds);
            }
        }
        return $this;
    }
}
