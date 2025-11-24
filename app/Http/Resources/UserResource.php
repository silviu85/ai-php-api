<?php
// File: app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method defines the public-facing shape of the User model.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // We only return the fields that are safe and necessary for the client.
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            // We can add other safe, computed properties here if needed.
            // For example: 'initials' => strtoupper(substr($this->name, 0, 2)),
        ];
    }
}