<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class spaceTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        // Deep check the link element.
        $link = (
            array_key_exists('links', $this->item)
            && array_key_exists('article_link', $this->item['links'])
        ) ? $this->item['links']['article_link'] : '';

        // Transform item.
        $data = [
            'number'  => $this->item['flight_number'] ?? '',
            'date'    => ( isset($this->dated_at) && is_a($this->dated_at, Carbon::class) ) ? $this->dated_at->toDateString() : '',
            'name'    => $this->item['mission_name'] ?? '',
            'link'    => $link,
            'details' => $this->item['details'] ?? '',
        ];

        return $data;
    }
}
