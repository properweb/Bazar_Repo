<?php

namespace Modules\Customer\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Retailer\Entities\Retailer;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Cart\Entities\Cart;
use Modules\Customer\Entities\Customer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;


class CustomerService
{
    protected Customer $customer;
    protected Brand $brand;
    private string $brandAbsPath = "";
    private string $brandRelPath = "";

    public function __construct()
    {
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }

    /**
     * Save new customer
     *
     * @param array $requestData
     * @return array
     */
    public function store(array $requestData): array
    {
        $user = auth()->user();
        //set request data with authenticated user id.
        if (!empty($requestData['customers'])) {
            foreach ($requestData['customers'] as $customer) {
                $customerData = $customer;
                $nameParts = explode(' ', $customer['name']);
                $first_name = $nameParts[0] ?? '';
                $last_name = '';
                if (isset($nameParts[1])) {
                    unset($nameParts[0]);
                    $last_name = implode(' ', $nameParts);
                }
                $customerData["shipping_name"] = $customer['name'];
                $customerData["first_name"] = $first_name;
                $customerData["last_name"] = $last_name;
                $customerData["user_id"] = $user->id;
                $customerData["source"] = 'Manual Upload';
                $customerData["status"] = Customer::STATUS;
                $customerData["reference"] = Customer::REFERENCE;
                $this->createCustomer($customerData);
            }
        }

        return [
            'res' => true,
            'msg' => 'Your customer created successfully',
            'data' => ''
        ];
    }

    /**
     * Create new customer
     *
     * @param array $customerData
     * @return Customer
     */
    public function createCustomer(array $customerData): Customer
    {
        //create customer
        $existCustomer = Customer::where('email', $customerData['email'])->first();
        if ($existCustomer) {
            $existCustomer->update($customerData);
            $customer = $existCustomer;
        } else {
            $customerData["customer_key"] = 'bc_' . Str::lower(Str::random(10));
            $existUser = User::where('email', $customerData['email'])->where('role', User::ROLE_RETAILER)->first();
            if ($existUser) {
                $customerData["retailer_id"] = $existUser->id;
                $customerData["first_name"] = $existUser->first_name;
                $customerData["last_name"] = $existUser->last_name;
                $retailer = Retailer::find('user_id', $existUser->id)->first();
                $customerData["store_name"] = $retailer->store_name;
                $customerData["source"] = 'Marketplace';
            }
            $newCustomer = new Customer();
            $newCustomer->fill($customerData);
            $newCustomer->save();
            $customer = $newCustomer;
        }

        return $customer;
    }

    /**
     * Update the specified customer in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function update(array $requestData): array
    {

        $customer = Customer::where('customer_key', $requestData['customer_key'])->first();

        // return error if no customer found
        if (!$customer) {
            return [
                'res' => false,
                'msg' => 'Customer not found !',
                'data' => ""
            ];
        }

        $nameArray = explode(' ', $requestData['cust_name']);
        $first_name = $nameArray[0] ?? '';
        $last_name = implode(' ', array_shift($nameArray));
        $customer->first_name = $first_name;
        $customer->last_name = $last_name;
        $customer->shipping_name = $requestData['cust_name'];
        $customer->email = $requestData['cust_email'];
        $customer->store_name = $requestData['cust_storename'];
        $customer->type = $requestData['cust_type'];
        $customer->save();

        return [
            'res' => true,
            'msg' => 'Your customer updated successfully',
            'data' => $customer
        ];
    }

    /**
     * save the specified customer's details in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function save(array $requestData): array
    {

        $customer = Customer::where('customer_key', $requestData['customer_key'])->first();

        // return error if no customer found
        if (!$customer) {
            return [
                'res' => false,
                'msg' => 'Customer not found !',
                'data' => ""
            ];
        }

        $customer->update($requestData);

        return [
            'res' => true,
            'msg' => 'Your customer updated successfully',
            'data' => $customer
        ];
    }

    /**
     * Save new customer
     *
     * @param array $requestData
     * @return array
     */
    public function create(array $requestData): array
    {
        $user = auth()->user();

        $requestData["user_id"] = $user->id;
        $requestData["status"] = Customer::STATUS;
        $requestData["source"] = Customer::SOURCE;
        $requestData["reference"] = Customer::REFERENCE;
        $customer = $this->createCustomer($requestData);

        return [
            'res' => true,
            'msg' => 'Your customer created successfully',
            'data' => $customer
        ];
    }

    /**
     * Get all customers
     *
     * @param Request $request
     * @return array
     */
    public function getAllCustomers(): array
    {
        $user = auth()->user();
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allCustomersCount = Customer::auth($brand->user_id)->count();
            $orderedCustomers = Customer::join(DB::raw('(SELECT user_id FROM orders GROUP BY user_id)orders'),
                function ($join) {
                    $join->on('orders.user_id', '=', 'customers.retailer_id');
                })
                ->where('customers.user_id', $brand->user_id)
                ->get();
            $orderedCustomersCount = $orderedCustomers->count();
            $contactedCustomers = Customer::join(DB::raw('(SELECT customer_id FROM `campaign_recipents` GROUP BY `customer_id`)campaign_recipents'),
                function ($join) {
                    $join->on('campaign_recipents.customer_id', '=', 'customers.id');
                })
                ->where('customers.user_id', $brand->user_id)
                ->get();
            $contactedCustomersCount = $contactedCustomers->count();
            $unusedCreditCustomersCount = Customer::auth($brand->user_id)->where('status', 'unused credit')->count();
            $notOrderedCustomersCount = Customer::auth($brand->user_id)->where('retailer_id', 'NULL')->count();
            $uncontactedCustomersCount = Customer::auth($brand->user_id)->where('status', 'uncontacted')->count();
            $notSignedCustomersCount = Customer::auth($brand->user_id)->where('status', 'not signed up')->count();
            $onBazarCustomersCount = Customer::auth($brand->user_id)->whereNotNull('retailer_id')->count();
            $customers = Customer::auth($brand->user_id)->get();
            $rcustomers = [];
            if ($customers) {
                foreach ($customers as $customer) {
                    $rcustomers[] = array(
                        'customer_key' => $customer->customer_key,
                        'name' => $customer->first_name . ' ' . $customer->last_name,
                        'email' => $customer->email,
                    );
                }
            }
            $data = array(
                "customers" => $rcustomers,
                "allCustomersCount" => $allCustomersCount,
                "orderedCustomersCount" => $orderedCustomersCount,
                "contactedCustomersCount" => $contactedCustomersCount,
                "unusedCreditCustomersCount" => $unusedCreditCustomersCount,
                "notOrderedCustomersCount" => $notOrderedCustomersCount,
                "uncontactedCustomersCount" => $uncontactedCustomersCount,
                "notSignedCustomersCount" => $notSignedCustomersCount,
                "onBazarCustomersCount" => $onBazarCustomersCount,
            );
            return ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            return ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
    }

    /**
     * Get the specified customer
     *
     * @param string $customerKey
     * @return array
     */
    public function get(string $customerKey): array
    {

        $customer = Customer::where('customer_key', $customerKey)->first();

        // return error if no customer found
        if (!$customer) {
            return [
                'res' => false,
                'msg' => 'Customer not found !',
                'data' => ""
            ];
        }

        return [
            'res' => true,
            'msg' => '',
            'data' => $customer
        ];
    }

    /**
     * Get sorted customers
     *
     * @param Request $request
     * @return array
     */
    public function getSortedCustomers(Request $request): array
    {
        $user = auth()->user();
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allCustomersCount = Customer::auth($brand->user_id)->count();
            $orderedCustomers = Customer::join(DB::raw('(SELECT user_id FROM orders GROUP BY user_id)orders'),
                function ($join) {
                    $join->on('orders.user_id', '=', 'customers.retailer_id');
                })
                ->where('customers.user_id', $brand->user_id)
                ->get();
            $orderedCustomersCount = $orderedCustomers->count();
            $contactedCustomers = Customer::join(DB::raw('(SELECT customer_id FROM `campaign_recipents` GROUP BY `customer_id`)campaign_recipents'),
                function ($join) {
                    $join->on('campaign_recipents.customer_id', '=', 'customers.id');
                })
                ->where('customers.user_id', $brand->user_id)
                ->get();
            $contactedCustomersCount = $contactedCustomers->count();
            $unusedCreditCustomersCount = Customer::auth($brand->user_id)->where('status', 'unused credit')->count();
            $notOrderedCustomersCount = Customer::auth($brand->user_id)->whereNull('retailer_id')->count();
            $uncontactedCustomersCount = Customer::auth($brand->user_id)->where('status', 'uncontacted')->count();
            $notSignedCustomersCount = Customer::auth($brand->user_id)->where('status', 'not signed up')->count();
            $onBazarCustomersCount = Customer::auth($brand->user_id)->whereNotNull('retailer_id')->count();
            $customers = Customer::auth($brand->user_id);
            $status = strtolower($request->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $customers->where('customers.status', $status);
                    break;
            }
            if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
                $customers->where('first_name', 'Like', '%' . $request->search_key . '%');
                $customers->orWhere('last_name', 'Like', '%' . $request->search_key . '%');
                $customers->orWhere('store_name', 'Like', '%' . $request->search_key . '%');
                $customers->orWhere('email', 'Like', '%' . $request->search_key . '%');
            }
            switch ($request->sort_key) {
                case 1:
                    $date = \Carbon\Carbon::today()->subDays(7);
                    $customers->where('created_at', '>=', $date);
                    break;
                case 2:
                    $customers->whereMonth('created_at', date('m'));
                    $customers->whereYear('created_at', date('Y'));
                    break;
                case 3:
                    $customers->whereMonth('created_at', '=', Carbon::now()->subMonth()->month);
                    break;
                default:
                    break;
            }
            $pcustomers = $customers->paginate(10);
            $rcustomers = [];
            if ($pcustomers) {
                foreach ($pcustomers as $customer) {
                    $cart_amount = 0;
                    $ordered_amount = 0;
                    $user = User::where('email', $customer->email)->first();
                    if ($user) {
                        $cart_amount = Cart::where('user_id', $brand->id)->where('user_id', $user->id)->where('order_id', '!=', null)->sum('amount');
                        $ordered_amount = Cart::where('user_id', $brand->id)->where('user_id', $user->id)->where('order_id', '!=', null)->sum('amount');
                    }
                    $rcustomers[] = array(
                        'customer_key' => $customer->customer_key,
                        'type' => $customer->type,
                        'name' => $customer->first_name . ' ' . $customer->last_name,
                        'email' => $customer->email,
                        'store_name' => $customer->store_name,
                        'source' => $customer->source,
                        'cart_amount' => $cart_amount,
                        'ordered_amount' => $ordered_amount
                    );
                }
            }
            $data = array(
                "customers" => $rcustomers,
                "allCustomersCount" => $allCustomersCount,
                "orderedCustomersCount" => $orderedCustomersCount,
                "contactedCustomersCount" => $contactedCustomersCount,
                "unusedCreditCustomersCount" => $unusedCreditCustomersCount,
                "notOrderedCustomersCount" => $notOrderedCustomersCount,
                "uncontactedCustomersCount" => $uncontactedCustomersCount,
                "notSignedCustomersCount" => $notSignedCustomersCount,
                "onBazarCustomersCount" => $onBazarCustomersCount,
            );
            return ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            return ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param array $requestData
     * @return array
     */
    public function delete(array $requestData): array
    {
        $user = auth()->user();
        if (!empty($requestData['customers'])) {
            foreach ($requestData['customers'] as $customerKey) {
                $customer = Customer::where('customer_key', $customerKey)->first();
                if ($customer) {
                    if ($user->can('delete', $customer)) {
                        $customer->delete();
                    }
                }
            }
        }

        return [
            'res' => true,
            'msg' => 'Customer successfully deleted.',
            'data' => ""
        ];
    }

    /**
     * Store imported customers
     *
     * @param array $requestData
     * @return array
     */
    public function importCustomers(array $requestData): array
    {

        $user = auth()->user();
        $brand = Brand::where('user_id', $user->id)->first();
        $brandId = $brand->id;
        $brandAbsPath = $this->brandAbsPath . "/" . $brandId . "/";

        $file = $requestData['upload_contact_list'];
        $fileName = Str::random(10) . '_cstmrs.' . $file->extension();
        $reference = $file->getClientOriginalName();
        $file->move($brandAbsPath, $fileName);
        $reader = new ReaderXlsx();
        $spreadsheet = $reader->load($brandAbsPath . $fileName);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unset($sheet[1]);
        foreach ($sheet as $data) {
            if ($data['D'] != '') {
                $customerData["store_name"] = $data['B'];
                $customerData["shipping_name"] = $data['C'];
                $nameArray = explode(' ', $data['C']);
                $first_name = $nameArray[0] ?? '';
                $last_name = implode(' ', array_shift($nameArray));
                $customerData["first_name"] = $first_name;
                $customerData["last_name"] = $last_name;
                $customerData["email"] = $data['D'];
                $customerData["user_id"] = $user->id;
                $customerData["status"] = Customer::STATUS;
                $customerData["source"] = Customer::SOURCE;
                $customerData["reference"] = $reference;
                $this->createCustomer($customerData);
            }
        }

        return ['res' => true, 'msg' => "Import Successfully", 'data' => ""];
    }

    /**
     * Export selected customers
     *
     * @param Request $request
     * @return array
     */

    public function exportCustomers(Request $request): array
    {
        $user = auth()->user();
        $brand = Brand::where('user_id', $user->id)->first();
        $brandId = $brand->id;
        $brandAbsPath = $this->brandAbsPath . "/" . $brandId . "/";
        $brandRelPath = $this->brandRelPath . $brandId . "/";
        $exportData = [];

        $customers = $request->customers;
        if (count($customers) > 0) {
            foreach ($customers as $customer) {
                $customerDetails = Customer::where('customer_key', $customer)->first();
                $exportData[] = array(
                    "store_name" => $customerDetails->store_name,
                    "name" => $customerDetails->fisrt_name . ' ' . $customerDetails->last_name,
                    "email" => $customerDetails->email,
                );
            }
        } else {
            $customers = Customer::where('user_id', $request->user_id)->get();
            if ($customers) {
                foreach ($customers as $customer) {
                    $exportData[] = array(
                        "store_name" => $customer->store_name,
                        "name" => $customer->fisrt_name . ' ' . $customer->last_name,
                        "email" => $customer->email,
                    );
                }
            }
        }
        if (count($exportData) > 0) {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->setActiveSheetIndex(0);
            $spreadSheet->getActiveSheet()->setPrintGridlines(true);
            $spreadSheet->getActiveSheet()->SetCellValue('A1', 'ID');
            $spreadSheet->getActiveSheet()->SetCellValue('B1', 'Store Name');
            $spreadSheet->getActiveSheet()->SetCellValue('C1', 'Name');
            $spreadSheet->getActiveSheet()->SetCellValue('D1', 'Email Address');
            $rowCount = 2;
            $rowIndex = 1;
            foreach ($exportData as $dataVal) {
                $spreadSheet->getActiveSheet()->SetCellValue('A' . $rowCount, $rowIndex);
                $spreadSheet->getActiveSheet()->SetCellValue('B' . $rowCount, $dataVal['store_name']);
                $spreadSheet->getActiveSheet()->SetCellValue('C' . $rowCount, $dataVal['name']);
                $spreadSheet->getActiveSheet()->SetCellValue('D' . $rowCount, $dataVal['email']);
                $rowCount++;
                $rowIndex++;
            }
            $fileName = 'customers_' . Str::random(10) . ".xlsx";
            $folderPath = $brandAbsPath . $fileName;
            $objWriter = IOFactory::createWriter($spreadSheet, 'Xlsx');
            $objWriter->save($folderPath);
            $fileDestination = asset('public') . '/' . $brandRelPath . $fileName;
            return ['res' => true, 'msg' => "Customers exported successfully", 'data' => $fileDestination];
        } else {
            return ['res' => false, 'msg' => "No customers to export", 'data' => ""];
        }
    }
}
