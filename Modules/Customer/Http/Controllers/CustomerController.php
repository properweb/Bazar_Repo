<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Http\Requests\ImportCustomerRequest;
use Modules\Customer\Http\Services\CustomerService;


class CustomerController extends Controller
{

    private CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Get list of customers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user is not a brand
        if ($user->cannot('viewAny', Customer::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $request->request->add(['user_id' => $user->id]);
        $response = $this->customerService->getCustomers($request);

        return response()->json($response);
    }

    /**
     * Store a newly created customer in storage
     *
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user cannot create customer
        if ($user->cannot('create', Customer::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customerService->store($request->validated());

        return response()->json($response);
    }

    /**
     * Fetch the specified customer
     *
     * @param string $customerKey
     * @return JsonResponse
     */
    public function show(string $customerKey): JsonResponse
    {

        $user = auth()->user();
        $customer = Customer::where('customer_key', $customerKey)->first();

        // return error if user not created the customer
        if ($user->cannot('view', $customer)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->customerService->get($customerKey);

        return response()->json($response);
    }

    /**
     * Update the specified customer in storage
     *
     * @param UpdateCustomerRequest $request
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request): JsonResponse
    {
        $user = auth()->user();
        $customer = Customer::where('customer_key', $request->customer_key)->first();

        // return error if user cannot update customer
        if ($user->cannot('update', $customer)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->customerService->update($request->validated());

        return response()->json($response);
    }

    /**
     * Remove the customers from storage
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user is not a brand
        if ($user->cannot('viewAny', Customer::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->customerService->delete($request->all());

        return response()->json($response);
    }

    /**
     * Store imported customers in storage
     *
     * @param ImportCustomerRequest $request
     * @return JsonResponse
     */
    public function import(ImportCustomerRequest $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user cannot create customer
        if ($user->cannot('create', Customer::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customerService->importCustomers($request->validated());

        return response()->json($response);
    }

    /**
     * Get list of customers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user is not a brand
        if ($user->cannot('viewAny', Customer::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customerService->exportCustomers($request);

        return response()->json($response);
    }
}
