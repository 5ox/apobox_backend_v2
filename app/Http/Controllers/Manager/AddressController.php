<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    /**
     * Store a new address for a customer (admin context).
     */
    public function store(StoreAddressRequest $request, int $customerId): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
