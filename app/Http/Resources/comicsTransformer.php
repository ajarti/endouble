<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class comicsTransformer extends JsonResource
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
        // Transform item.
        $data = [
            'number'  => $this->item['num'] ?? '',
            'date'    => ( isset($this->dated_at) && is_a($this->dated_at, Carbon::class) ) ? $this->dated_at->toDateString() : '',
            'name'    => $this->item['title'] ?? '',
            'link'    => $this->item['img'] ?? '',
            'details' => $this->item['alt'] ?? '',
        ];

        return $data;
    }
}
