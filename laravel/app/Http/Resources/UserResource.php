<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => substr($this->name, 0, 16),
            'phone' => $this->maskPhoneNumber($this->phone)
        ];
    }

    /**
     * 手机号码打码处理
     *
     * @param string|null $phoneNumber
     * @return string|null
     */
    protected function maskPhoneNumber(?string $phoneNumber): ?string
    {
        if (!$phoneNumber) {
            return null;
        }
        // 保留前三位和后四位，中间用星号替代
        return substr($phoneNumber, 0, 3) . '****' . substr($phoneNumber, -4);
    }


}
