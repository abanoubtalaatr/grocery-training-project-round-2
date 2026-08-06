<?php 
namespace App\Action\Api;

use App\Models\Address;

class UpdateAddressAction
{
    public function execute( Address $address, array $data)
    {
        return  $address->update($data);
    }
}