<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->storageUrl($this->avatar),
            'email_verified_at' => $this->iso($this->email_verified_at),
            'created_at' => $this->iso($this->created_at),
            'updated_at' => $this->iso($this->updated_at),
            
            // Relations
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('name');
            }),
        ];
    }
}
