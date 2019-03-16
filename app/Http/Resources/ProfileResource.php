<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            // 'phone' => $this->phone,
            // 'sur_name' => $this->sur_name,
            // 'personal_email' => $this->personal_email,
            // 'language' => $this->language,
            // 'notification' => $this->notification,
            // 'contact_type' => $this->contact_type
        ];
    }
}
