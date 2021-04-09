<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,
            'valid_from' => $this->valid_from,
            'valid_to'   => $this->valid_to,
            'gps_lat'    => $this->gps_lat,
            'gps_lng'    => $this->gps_lng,
            'comments'   => CommentResource::collection($this->comments)
        ];
    }
}
