<?php

namespace App\Http\Controllers\PaymentMethods;

use Illuminate\Http\Request;
use App\Cart\Payments\Gateway;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->middleware(['auth:api']);
        $this->gateway = $gateway;
    }

    public function index(Request $request)
    {
        return PaymentMethodResource::collection($request->user()->paymentMethods);
    }

    /**
     * Storing Gateway with stripe
     *
     * @param Request $request
     * @return $cart | instanceOf PaymentMethod::class
     */
    public function store(Request $request)
    {
        $cart = $this->gateway->withUser($request->user())
            ->createCustomer()
            ->addCard($request->token);
    }
}
