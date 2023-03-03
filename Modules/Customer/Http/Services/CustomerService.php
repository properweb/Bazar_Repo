<?php

namespace Modules\Customer\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Customer\Entities\Customer;


class CustomerService
{
    protected Customer $customer;

    protected Brand $brand;

    /**
     * Save order
     *
     * @param array $request
     * @return array
     */
    public function store(array $requestData): array
    {
        DB::beginTransaction();

        try {
            $this->customer = $this->createCustomer($requestData);

            $response = [
                'res' => true,
                'msg' => 'Your customer created successfully',
                'data' => ""
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => 'Someting went wrong !',
                'data' => ""
            ];

        }

        return $response;
    }

    /**
     * Create new customer
     *
     * @param  array  $customerData
     * @return Customer
     */
    public function createCustomer(array $customerData): Customer
    {
        //create customer
        $customer = new Customer();
        $customer->brand_id = $customerData['user_id'];
        $customer->customer_key = 'bmc_' . Str::lower(Str::random(10));
        $customer->title = $customerData['title'];
        $customer->save();

        return $customer;
    }

    /**
     * Get all Customers
     *
     * @return array
     */
    public function getCustomers($requestData): array
    {
        $user = User::find($requestData->user_id);
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allCustomersCount = Customer::where('brand_id', $brand->user_id)->count();
            $draftCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'draft')->count();
            $scheduledCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'schedule')->count();
            $completedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'completed')->count();
            $customers = Customer::where('brand_id', $brand->user_id);
            $status = strtolower($requestData->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $customers->where('status', $status);
                    break;
            }
            $paginatedCustomers = $customers->paginate(10);
            $filteredCustomers = [];
            if ($paginatedCustomers) {
                foreach ($paginatedCustomers as $customer) {
                    $filteredCustomers[] = array(
                        'title' => $customer->title,
                        'customer_key' => $customer->customer_key,
                        'updated_at' => date("F j, Y, g:i a", strtotime($customer->updated_at)),
                    );
                }
            }
            $data = array(
                "customers" => $filteredCustomers,
                "allCustomersCount" => $allCustomersCount,
                "draftCustomersCount" => $draftCustomersCount,
                "scheduledCustomersCount" => $scheduledCustomersCount,
                "completedCustomersCount" => $completedCustomersCount,
            );
            $response = ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        
        

        return $response;
    }
    
    /**
     * Delete customer
     *
     * @param array $request
     * @return array
     */
    public function delete( $customerKey): 
    {
        $customer = Customer::where('customer_key', $customerKey)->first();
        

        // return error if no customer found
        if (empty($customer)) {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }

        DB::beginTransaction();

        try {
            $this->customer = Customer::where('customer_key', $customerKey)->first();

            $this->deleteCustomer($customer);

            
            $response = [
                'res' => true,
                'msg' => 'Customer successfully deleted',
                'data' => ""
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => 'Error while deleting customer !',
                'data' => ""
            ];

        }

        return $response;
    }
    
    /**
     * @param Product|null $existingProduct
     */
    private function deleteCustomer(DeleteCustomer $deleteCustomer): void
    {
        $deleteCustomer->delete();
    }

    
}
