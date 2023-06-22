<?php

namespace Modules\Custom\Http\Services;

use Modules\Brand\Entities\Brand;
use Modules\Country\Entities\Country;
use Modules\Product\Entities\Product;
use Illuminate\Support\Str;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;
use Modules\User\Entities\User;
use Modules\Wordpress\Entities\Store;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class CustomService
{
    private $productAbsPath = "";
    private $productRelPath = "";
    protected Product $product;


    public function __construct()
    {
        $this->productAbsPath = public_path('uploads/products/');
        $this->productRelPath = asset('public') . '/uploads/products/';
    }

    /**
     * Add custom API details
     *
     * @param  $requestData
     * @return array
     */
    public function addApi($requestData): array
    {

        $userId = auth()->user()->id;
        $remove = array("http://", "https://", "www.", "/");
        $brandStore = new Store;
        $brandStore->brand_id = $userId;
        $brandStore->website = $requestData['store_url'];
        $brandStore->api_key = $requestData['api_key'];
        $brandStore->api_password = $requestData['api_password'];
        $brandStore->types = 'Custom API';
        $brandStore->location_id = '';
        $brandStore->url = str_replace($remove, "", $requestData['store_url']);
        $brandStore->save();
        return ['res' => true, 'msg' => "Thanks for sharing details. It will takes 5-7 business working days to import data", 'data' => ""];
    }

    /**
     * Import custom website products
     *
     * @param array $request
     * @return array
     */
    public function importProduct(array $request): array
    {
        if ($request['upload_bulk_xlsx']->extension() != 'xlsx') {
            return ['res' => true, 'msg' => "Please upload valid xlsx file", 'data' => ""];
        }
        $userId = auth()->user()->id;
        $path = storage_path() . '/app/' . $request['upload_bulk_xlsx']->store('tmp');
        $reader = new ReaderXlsx();
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unset($sheet[1]);
        unset($sheet[2]);
        if (!empty($sheet)) {

            foreach ($sheet as $data) {

                $name = $data['A'];
                $description = $data['D'];
                $country = $data['E'];
                $caseQuantity = $data['F'];
                $minOrderQty = $data['G'];
                $sku = $data['I'];
                $usdWholesale = (float)$data['P'];
                $usdRetail = (float)$data['Q'];
                $cadWholesale = (float)$data['R'];
                $cadRetail = (float)$data['S'];
                $gbrWholesale = (float)$data['T'];
                $gbrRetail = (float)$data['U'];
                $eurWholesale = (float)$data['V'];
                $eurRetail = (float)$data['W'];
                $usdTester = (float)$data['X'];
                $fabricContent = $data['AC'];
                $careInstruction = $data['AD'];
                $season = $data['AE'];
                $occasion = $data['AF'];
                $aesthetic = $data['AG'];
                $fit = $data['AH'];
                $preorder = $data['AL'];
                $productShip = $data['AM'];
                $productEndShip = $data['AN'];
                $productDeadline = $data['AO'];
                $image1 = $data['Y'];
                $image2 = $data['Z'];
                $image3 = $data['AA'];
                $image4 = $data['AB'];

                $qryCountry = Country::where("name", $country)->first();
                if (!empty($qryCountry)) {
                    $countryId = $qryCountry->id;
                } else {
                    $countryId = 0;
                }
                $productKey = 'p_' . Str::lower(Str::random(10));
                $productSlug = Str::slug($name, '-');

                $featuredImage = '';
                $productImages = [];
                if (!empty($image1)) {
                    $productImages[] = strpos($image1, 'http') !== false ? $image1 : asset('public') . '/uploads/products/' . $image1;
                    $featuredImage = strpos($image1, 'http') !== false ? $image1 : asset('public') . '/uploads/products/' . $image1;
                }
                if (!empty($image2)) {
                    $productImages[] = strpos($image2, 'http') !== false ? $image2 : asset('public') . '/uploads/products/' . $image2;
                }
                if (!empty($image3)) {
                    $productImages[] = strpos($image3, 'http') !== false ? $image3 : asset('public') . '/uploads/products/' . $image3;
                }
                if (!empty($image4)) {
                    $productImages[] = strpos($image4, 'http') !== false ? $image4 : asset('public') . '/uploads/products/' . $image4;
                }


                $product = new Product();
                $product->product_key = $productKey;
                $product->slug = $productSlug;
                $product->import_type = 'Custom Website';
                $product->website = $request['store_url'];
                $product->name = $name;
                $product->user_id = auth()->user()->id;
                $product->status = "unpublish";
                $product->description = addslashes($description);
                $product->country = $countryId;
                $product->case_quantity = $caseQuantity ?? 0;
                $product->min_order_qty = $minOrderQty ?? 0;
                $product->sku = $sku;
                $product->usd_wholesale_price = $usdWholesale ?? 0;
                $product->usd_retail_price = $usdRetail ?? 0;
                $product->cad_wholesale_price = $cadWholesale ?? 0;
                $product->cad_retail_price = $cadRetail ?? 0;
                $product->eur_wholesale_price = $eurWholesale ?? 0;
                $product->eur_retail_price = $eurRetail ?? 0;
                $product->gbr_wholesale_price = $gbrWholesale ?? 0;
                $product->gbr_retail_price = $gbrRetail ?? 0;
                $product->usd_tester_price = $usdTester ?? 0;
                $product->care_instruction = $careInstruction;
                $product->season = $season;
                $product->Occasion = $occasion;
                $product->Aesthetic = $aesthetic;
                $product->Fit = $fit;
                $product->Preorder = $preorder;
                $product->featured_image = $featuredImage;
                $product->country = $countryId;
                $product->fabric_content = $fabricContent;
                $product->product_shipdate = date('Y-m-d', strtotime($productShip));
                $product->product_endshipdate = date('Y-m-d', strtotime($productEndShip));
                $product->product_deadline = date('Y-m-d', strtotime($productDeadline));
                $product->created_at = date('Y-m-d H:i:s');
                $product->updated_at = date('Y-m-d H:i:s');

                $product->save();
                $lastInsertId = $product->id;


                $optName1 = str_replace("'", '"', $data['J']);
                $optValue1 = str_replace("'", '"', $data['K']);
                $optName2 = str_replace("'", '"', $data['L']);
                $optValue2 = str_replace("'", '"', $data['M']);
                $optName3 = str_replace("'", '"', $data['N']);
                $optValue3 = str_replace("'", '"', $data['O']);
                $optionTypes = 0;
                if ($optName1 != '' && strtolower($optName1) != 'optional') {
                    $optionTypes++;
                }
                if ($optName2 != '' && strtolower($optName2) != 'optional') {
                    $optionTypes++;
                }
                if ($optName3 != '' && strtolower($optName3) != 'optional') {
                    $optionTypes++;
                }
                $variations = [];


                if ($optionTypes > 0) {
                    $option1Values = explode(',', $optValue1);
                    $option2Values = explode(',', $optValue2);
                    $option3Values = explode(',', $optValue3);
                    if (!empty($option3Values)) {
                        foreach ($option3Values as $ok3 => $ov3) {
                            if (!empty($option2Values)) {
                                foreach ($option2Values as $ok2 => $ov2) {
                                    if (!empty($option1Values)) {
                                        foreach ($option1Values as $ok1 => $ov1) {
                                            $variations[] = array(
                                                'option1' => $optName1, 'option2' => $optName2, 'option3' => $optName3, 'value1' => $ov1, 'value2' => $ov2, 'value3' => $ov3, 'swatch_image' => '', 'sku' => '', 'wholesale_price' => $usdWholesale, 'retail_price' => $usdRetail, 'inventory' => 0, 'weight' => 0, 'length' => 0, 'length_unit' => '', 'width_unit' => '', 'height_unit' => '', 'width' => 0, 'height' => 0, 'dimension_unit' => '', 'weight_unit' => '', 'tariff_code' => 0
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (is_countable($variations) && count($variations) > 0) {

                    foreach ($variations as $vars) {
                        $variantKey = 'v_' . Str::lower(Str::random(10));
                        $productVariation = new ProductVariation();
                        $productVariation->variant_key = $variantKey;
                        $productVariation->product_id = $lastInsertId;
                        $productVariation->price = $vars['wholesale_price'];
                        $productVariation->options1 = $vars['option1'];
                        $productVariation->options2 = $vars['option2'];
                        $productVariation->options3 = $vars['option3'];
                        $productVariation->sku = $vars['sku'];
                        $productVariation->value1 = $vars['value1'];
                        $productVariation->value2 = $vars['value2'];
                        $productVariation->value3 = $vars['value3'];
                        $productVariation->retail_price = $vars['retail_price'];
                        $productVariation->cad_wholesale_price = $cadWholesale;
                        $productVariation->cad_retail_price = $cadRetail;
                        $productVariation->eur_wholesale_price = $eurWholesale;
                        $productVariation->eur_retail_price = $eurRetail;
                        $productVariation->stock = $vars['inventory'];
                        $productVariation->weight = $vars['weight'];
                        $productVariation->length = $vars['length'];
                        $productVariation->length_unit = $vars['length_unit'];
                        $productVariation->width_unit = $vars['width_unit'];
                        $productVariation->height_unit = $vars['height_unit'];
                        $productVariation->width = $vars['width'];
                        $productVariation->height = $vars['height'];
                        $productVariation->dimension_unit = $vars['dimension_unit'];
                        $productVariation->weight_unit = $vars['weight_unit'];
                        $productVariation->website = $request['store_url'];
                        $productVariation->tariff_code = $vars['tariff_code'];
                        $productVariation->save();


                    }
                }

            }
        }
        $store = Store::where('website', $request['store_url'])->first();
        if (empty($store)) {
            $remove = array("http://", "https://", "www.", "/");
            $brandStore = new Store;
            $brandStore->brand_id = $userId;
            $brandStore->website = $request['store_url'];
            $brandStore->api_key = '';
            $brandStore->api_password = '';
            $brandStore->types = 'Custom Upload';
            $brandStore->location_id = '';
            $brandStore->url = str_replace($remove, "", $request['store_url']);
            $brandStore->save();
        }


        return ['res' => true, 'msg' => "Successfully Imported", 'data' => ""];
    }

    /**
     * Export product stock with sku
     *
     * @param $request
     * @return array
     */
    public function exportProduct($request): array
    {
        $userId = auth()->user()->id;
        $products = Product::where('user_id', $userId)->where('website', $request->store_url)->get();
        $variations = ProductVariation::where('website', $request->store_url)->get();
        $spreadSheet = new Spreadsheet();
        $spreadSheet->setActiveSheetIndex(0);
        $spreadSheet->getActiveSheet()->SetCellValue('A1', 'SKU');
        $this->cellColor("A1", 'b6d7a8', $spreadSheet);
        $spreadSheet->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
        $spreadSheet->getActiveSheet()->SetCellValue('B1', 'Stock');
        $this->cellColor("B1", 'b6d7a8', $spreadSheet);
        $spreadSheet->getActiveSheet()->setPrintGridlines(true);
        $spreadSheet->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $spreadSheet->getActiveSheet()->SetCellValue('C1', 'Product Name');
        $this->cellColor("C1", 'b6d7a8', $spreadSheet);
        $spreadSheet->getActiveSheet()->setPrintGridlines(true);
        $spreadSheet->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $rowCount = 2;
        if (!empty($products)) {
            foreach ($products as $v) {
                $spreadSheet->getActiveSheet()->SetCellValue('A' . $rowCount, $v->sku);
                $spreadSheet->getActiveSheet()->SetCellValue('B' . $rowCount, $v->stock ?? 0);
                $spreadSheet->getActiveSheet()->SetCellValue('C' . $rowCount, $v->name);
                $rowCount++;
            }
        }
        if (!empty($variations)) {
            $rowCount = $rowCount + 1;
            foreach ($variations as $v) {
                $spreadSheet->getActiveSheet()->SetCellValue('A' . $rowCount, $v->sku);
                $spreadSheet->getActiveSheet()->SetCellValue('B' . $rowCount, $v->stock ?? 0);
                $spreadSheet->getActiveSheet()->SetCellValue('C' . $rowCount, $v->value1 . ' ' . $v->value2 . ' ' . $v->value3);
                $rowCount++;
            }
        }
        foreach (range('A', 'B') as $column) {
            $spreadSheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(false);
            $spreadSheet->getActiveSheet()->getColumnDimension($column)->setWidth('30');
        }
        $fileName = rand() . ".xlsx";
        $folderPath = $this->productAbsPath . $fileName;
        $objWriter = IOFactory::createWriter($spreadSheet, 'Xlsx');
        $objWriter->save($folderPath);
        $file = $this->productRelPath . $fileName;
        return ['res' => true, 'msg' => "", 'data' => $file];

    }

    /**
     * Export product stock with sku
     *
     * @return array
     */
    public function fetchCustom(): array
    {
        $userId = auth()->user()->id;
        $customWebsite = Store::where('brand_id', $userId)->where('types', 'Custom Upload')->get();
        $all = [];
        foreach ($customWebsite as $v) {


            $all[] = array(
                'store_url' => $v->website,
            );

        }
        return ['res' => true, 'msg' => "", 'data' => $all];

    }

    /**
     * Update Stock
     *
     * @param $request
     * @return array
     */
    public function updateStock($request): array
    {
        if ($request->upload_bulk_xlsx->extension() != 'xlsx') {
            return ['res' => true, 'msg' => "Please upload valid xlsx file", 'data' => ""];
        }
        $path = storage_path() . '/app/' . $request->upload_bulk_xlsx->store('tmp');
        $reader = new ReaderXlsx();
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unset($sheet[1]);
        if (!empty($sheet)) {
            foreach ($sheet as $data) {
                $sku = $data['A'];
                $stock = $data['B'];
                Product::where('sku', $sku)->where('website', $request->store_url)
                    ->update([
                        'stock' => $stock
                    ]);
                ProductVariation::where('sku', $sku)->where('website', $request->store_url)
                    ->update([
                        'stock' => $stock
                    ]);
            }
        }


        return ['res' => true, 'msg' => "Successfully updated", 'data' => ""];
    }

    /**
     * Cell color library
     *
     * @param $cells
     * @param $color
     * @param $spreadSheet
     * @return bool
     */
    private function cellColor($cells, $color, $spreadSheet)
    {

        $spreadSheet->getActiveSheet()->getStyle($cells)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => $color
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 11
            ],

        ]);

        return true;
    }

    /**
     * Cell color libray
     *
     * @param $cells
     * @param $color
     * @param $spreadSheet
     * @return bool
     */
    private function cellColorSecond($cells, $color, $spreadSheet)
    {

        $spreadSheet->getActiveSheet()->getStyle($cells)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => $color
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'italic' => true,
                'size' => 11
            ],

        ]);

        return true;
    }

}
