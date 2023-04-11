<?php

namespace Modules\Customer\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        //set request data with authenticated user id.
        if (!empty($requestData['customers'])) {
            foreach ($requestData['customers'] as $customer) {
                $customerData["user_id"] = $requestData['user_id'];
                $customerData["status"] = Customer ::STATUS;
                $customerData["source"] = Customer ::SOURCE;
                $customerData["reference"] = Customer ::REFERENCE;
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
            $newCustomerData['name'] = $customerData['name'];
            $newCustomerData['store_name'] = $customerData['store_name'];
            $existCustomer->update($newCustomerData);
            $customer = $existCustomer;
        } else {
            $customerData["customer_key"] = 'bc_' . Str::lower(Str::random(10));
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

        $customer->update($requestData);

        return [
            'res' => true,
            'msg' => 'Your customer updated successfully',
            'data' => $customer
        ];
    }

    /**
     * Get all customers
     *
     * @param Request $request
     * @return array
     */
    public function getCustomers(Request $request): array
    {
        $user = User::find($request->user_id);
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allCustomersCount = Customer::where('brand_id', $brand->user_id)->count();
            $orderedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'ordered')->count();
            $contactedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'contacted')->count();
            $unusedCreditCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'unused credit')->count();
            $notOrderedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'not yet ordered')->count();
            $uncontactedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'uncontacted')->count();
            $notSignedCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'not signed up')->count();
            $onBazarCustomersCount = Customer::where('brand_id', $brand->user_id)->where('status', 'on bazar')->count();
            $customers = Customer::where('brand_id', $brand->user_id);
            $status = strtolower($request->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $customers->where('status', $status);
                    break;
            }
            if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
                $customers->where('name', 'Like', '%' . $request->search_key . '%');
                $customers->orWhere('store_name', 'Like', '%' . $request->search_key . '%');
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
                        $cart_amount = Cart::where('brand_id', $brand->id)->where('user_id', $user->id)->where('order_id', '!=', null)->sum('amount');
                        $ordered_amount = Cart::where('brand_id', $brand->id)->where('user_id', $user->id)->where('order_id', '!=', null)->sum('amount');
                    }
                    $rcustomers[] = array(
                        'customer_key' => $customer->customer_key,
                        'type' => $customer->type,
                        'name' => $customer->name,
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
     * @param string $customerKey
     * @return array
     */
    public function delete(string $customerKey): array
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

        $customer->delete();

        return [
            'res' => true,
            'msg' => 'Customer successfully deleted',
            'data' => ""
        ];
    }

    /**
     * Store imported customers
     *
     * @param Request $request
     * @return array
     */
    public function importCustomers(Request $request): array
    {
        $user = auth()->user();
        $brand = Brand::where('user_id', $user->id)->first();
        $brandId = $brand->id;
        $brandAbsPath = $this->brandAbsPath . "/" . $brandId . "/";

        $file = $request->file('upload_contact_list');
        $fileName = Str::random(10) . '_cstmrs.' . $file->extension();
        $file->move($brandAbsPath, $fileName);
        $reader = new ReaderXlsx();
        $spreadsheet = $reader->load($brandAbsPath . $fileName);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unset($sheet[1]);
        foreach ($sheet as $data) {
            $customerData["store_name"] = $data['B'];
            $customerData["name"] = $data['C'];
            $customerData["email"] = $data['D'];
            $customerData["user_id"] = $request['user_id'];
            $customerData["status"] = Customer ::STATUS;
            $customerData["source"] = Customer ::SOURCE;
            $customerData["reference"] = Customer ::REFERENCE;
            $this->createCustomer($customerData);
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
                    "name" => $customerDetails->name,
                    "email" => $customerDetails->email,
                );
            }
        } else {
            $customers = Customer::where('user_id', $request->user_id)->get();
            if ($customers) {
                foreach ($customers as $customer) {
                    $exportData[] = array(
                        "store_name" => $customer->store_name,
                        "name" => $customer->name,
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
            $fileDestination = $brandRelPath . $fileName;
            return ['res' => true, 'msg' => "Customers exported successfully", 'data' => $fileDestination];
        } else {
            return ['res' => false, 'msg' => "No customers to export", 'data' => ""];
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
}
