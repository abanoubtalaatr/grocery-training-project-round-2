<?php

namespace App\Actions\Api\Address;

use Illuminate\Http\Request;

class GetAllAdressesAction
{

    public function run(Request $request)
    {
        $user = $request->user();

        $addresses = $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return $addresses;
    }
}
