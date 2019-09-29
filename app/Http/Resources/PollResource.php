<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        self::withoutWrapping();
        return [
            'poll_id' => $this->id,
            'poll_description' => $this->description,
            'options' => array_map(function($value){
                return array(
                    'option_id' => $value['id'],
                    'option_description' => $value['description']
                );
            },$this->options->toArray())
        ];
    }
}
