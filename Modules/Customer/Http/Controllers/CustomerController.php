<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Customer\Entities\Customer;
use File;
use Carbon\Carbon;

class CustomerController extends Controller
{

    /**
     * @var
     */
    private $brandAbsPath = "";

    /**
     * @var string
     */
    private $brandRelPath = "";

    /**
     *
     */
    public function __construct()
    {
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = asset('public') . '/uploads/brands/';
    }

    /**
     * Get list of customers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
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
            $response = ['res' => true, 'msg' => "", 'data' => $data];
            return response()->json($response);
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
            return response()->json($response);
        }
    }

    /**
     * Store a newly created customer in storage
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $customers = $request->customers;
            if ($customers) {
                foreach ($customers as $customer) {
                    $exstCustomer = User::where('email', $customer['email_address'])->first();
                    if ($exstCustomer) {
                        $exstCustomer->name = $customer['contact_name'];
                        $exstCustomer->store_name = $customer['store_name'];
                        $exstCustomer->save();
                    } else {
                        $newCustomer = new Customer;
                        $newCustomer->brand_id = $request->user_id;
                        $newCustomer->customer_key = 'bc_' . Str::lower(Str::random(10));
                        $newCustomer->name = $customer['contact_name'];
                        $newCustomer->store_name = $customer['store_name'];
                        $newCustomer->email = $customer['email_address'];
                        $newCustomer->save();
                    }
                }
            }
            $response = ['res' => true, 'msg' => "Customers inserted successfully", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * store imported customers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $brand = Brand::where('user_id', $request->user_id)->first();
        $brandId = $brand->id;
        $brandAbsPath = $this->brandAbsPath . "/" . $brandId . "/";
        $brandRelPath = $this->brandRelPath . $brandId . "/";

        if ($request->file('upload_contact_list')->extension() == 'xlsx') {
            $file = $request->file('upload_contact_list');
            $xlsxName = $file->getClientOriginalName();
            $fileName = Str::random(10) . '_cstmrs.' . $file->extension();
            $file->move($brandAbsPath, $fileName);
            $reader = new ReaderXlsx();
            $spreadsheet = $reader->load($brandAbsPath . $fileName);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            unset($sheet[1]);
            foreach ($sheet as $data) {
                $id = $data['A'];
                $store_name = $data['B'];
                $name = $data['C'];
                $email = $data['D'];
                $customer = Customer::where('email', $email)->first();
                if ($customer) {
                    $referenceStr = $customer->reference;
                    $referenceArr = explode(',', $referenceStr);
                    $referenceArr[] = $xlsxName;
                    $referenceStr = implode(',', $referenceArr);
                    $customer->name = $name;
                    $customer->store_name = $store_name;
                    $customer->reference = $referenceStr;
                    $customer->save();
                } else {
                    $newCustomer = new Customer;
                    $newCustomer->brand_id = $request->user_id;
                    $newCustomer->customer_key = 'bc_' . Str::lower(Str::random(10));
                    $newCustomer->name = $name;
                    $newCustomer->store_name = $store_name;
                    $newCustomer->email = $email;
                    $newCustomer->reference = $xlsxName;
                    $newCustomer->save();
                }
            }

            $response = ['res' => true, 'msg' => "Import Successfully", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "Please upload xlsx format", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Fetch the specified customer
     *
     * @param string $customerKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($customerKey)
    {
        $customer = Customer::where('customer_key', $customerKey)->first();
        if ($customer) {
            $response = ['res' => true, 'msg' => "", 'data' => $customer];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Update the specified customer in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $customer = Customer::where('customer_key', $request->customer_key)->first();
        if ($customer) {
            $customer->email = $request->cust_email;
            $customer->type = $request->cust_type;
            $customer->name = $request->cust_name;
            $customer->store_name = $request->cust_storename;
            $customer->save();
            $response = ['res' => true, 'msg' => "", 'data' => $customer];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Remove multiple customers from storage
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $customers = $request->customers;
        if ($customers) {
            foreach ($customers as $customer) {
                $customerDetails = Customer::where('customer_key', $customer)->first();
                $customerDetails->delete();
            }
            $response = ['res' => true, 'msg' => "Customer successfully deleted", 'data' => ""];
            return response()->json($response);
        }
    }

    /**
     * Export selected customers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {

        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
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
                $customers = Customer::where('brand_id', $request->user_id)->get();
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
                $response = ['res' => true, 'msg' => "Customers exported successfully", 'data' => $fileDestination];
            } else {
                $response = ['res' => false, 'msg' => "No customers to export", 'data' => ""];
            }
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

}
