<?php

namespace Modules\ImportExport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Redis;
use Modules\Country\Entities\Country;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\Category;
use Illuminate\Support\Str;
use DB;


class ImportExportController extends Controller
{
    private $absPath = "";
    private $realPath = "";

    public function __construct()
    {
        $this->absPath = public_path('uploads/');
        $this->realPath = asset('public') . '/uploads/';

        Redis::connection();
    }

    public function index(Request $request)
    {
        if ($request->file('upload_bulk_xlsx')->extension() == 'xlsx') {
            $path = storage_path() . '/app/' . request()->file('upload_bulk_xlsx')->store('tmp');

            $reader = new ReaderXlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            unset($sheet[2]);
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


                $productSlug = Str::slug($name, '-');

                $qryProduct = Products::where('slug', $productSlug)->first();

                if (empty($qryProduct)) {

                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($name, '-');

                    $featuredImage = '';
                    $productImages = [];
                    if (!empty($image1)) {
                        $productImages[] = $image1 ? $image1 : '';
                        $featuredImage = $image1 ? $image1 : '';
                    }
                    if (!empty($image2)) {
                        $productImages[] = $image2 ? $image2 : '';
                    }
                    if (!empty($image3)) {
                        $productImages[] = $image3 ? $image3 : '';
                    }
                    if (!empty($image4)) {
                        $productImages[] = $image4 ? $image4 : '';
                    }


                    $product = new Products();
                    $product->product_key = $productKey;
                    $product->slug = $productSlug;
                    $product->name = $name;
                    $product->user_id = $request->input('user_id');
                    $product->status = "unpublish";
                    $product->description = addslashes($description);
                    $product->country = $countryId;
                    $product->case_quantity = $caseQuantity;
                    $product->min_order_qty = $minOrderQty;
                    $product->sku = $sku;
                    $product->usd_wholesale_price = $usdWholesale;
                    $product->usd_retail_price = $usdRetail;
                    $product->cad_wholesale_price = $cadWholesale;
                    $product->cad_retail_price = $cadRetail;
                    $product->eur_wholesale_price = $eurWholesale;
                    $product->eur_retail_price = $eurRetail;
                    $product->gbr_wholesale_price = $gbrWholesale;
                    $product->gbr_retail_price = $gbrRetail;
                    $product->usd_tester_price = $usdTester;
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
                    $lastInsertId = DB::getPdo()->lastInsertId();


                    if (!empty($productImages)) {
                        foreach ($productImages as $imgK => $img) {
                            $featureKey = $imgK == 0 ? 1 : 0;
                            $productImage = new ProductImage();
                            $productImage->product_id = $lastInsertId;
                            $productImage->images = $img;
                            $productImage->feature_key = $featureKey;
                            $productImage->save();

                        }
                    }

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
                            $productVariation->tariff_code = $vars['tariff_code'];
                            $productVariation->save();


                        }
                    }


                }

            }
            Redis::flushDB();
            $response = ['res' => true, 'msg' => "Import Successfully", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "Please upload xlsx format", 'data' => ""];
        }
        return response()->json($response);
    }

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

    public function export(Request $request)
    {
        $products = Products::where('user_id', $request->user_id)
            ->orderBy('order_by', 'ASC')
            ->get();
        $spreadSheet = new Spreadsheet();


        $spreadSheet->setActiveSheetIndex(0);
        $spreadSheet->getActiveSheet()->SetCellValue('A1', 'Product Name');
        $spreadSheet->getActiveSheet()->SetCellValue('B1', 'Product Status');
        $spreadSheet->getActiveSheet()->SetCellValue('C1', 'Product Type');
        $spreadSheet->getActiveSheet()->SetCellValue('D1', 'Description');
        $spreadSheet->getActiveSheet()->SetCellValue('E1', 'Made In Country');
        $spreadSheet->getActiveSheet()->SetCellValue('F1', 'Case Quantity');
        $spreadSheet->getActiveSheet()->SetCellValue('G1', 'Minimum Order Quantity');
        $spreadSheet->getActiveSheet()->SetCellValue('H1', 'Minimum Order Quantity Type');
        $spreadSheet->getActiveSheet()->SetCellValue('I1', 'SKU');
        $spreadSheet->getActiveSheet()->SetCellValue('J1', 'Option 1 Name');
        $spreadSheet->getActiveSheet()->SetCellValue('K1', 'Option 1 Value');
        $spreadSheet->getActiveSheet()->SetCellValue('L1', 'Option 2 Name');
        $spreadSheet->getActiveSheet()->SetCellValue('M1', 'Option 2 Value');
        $spreadSheet->getActiveSheet()->SetCellValue('N1', 'Option 3 Name');
        $spreadSheet->getActiveSheet()->SetCellValue('O1', 'Option 3 Value');
        $spreadSheet->getActiveSheet()->SetCellValue('P1', 'USD Unit Wholesale Price');
        $spreadSheet->getActiveSheet()->SetCellValue('Q1', 'USD Unit Retail Price');
        $spreadSheet->getActiveSheet()->SetCellValue('R1', 'CAD Unit Wholesale Price');
        $spreadSheet->getActiveSheet()->SetCellValue('S1', 'CAD Unit Retail Price');
        $spreadSheet->getActiveSheet()->SetCellValue('T1', 'GBR Unit Wholesale Price');
        $spreadSheet->getActiveSheet()->SetCellValue('U1', 'GBR Unit Retail Price');
        $spreadSheet->getActiveSheet()->SetCellValue('V1', 'EUR Unit Wholesale Price');
        $spreadSheet->getActiveSheet()->SetCellValue('W1', 'EUR Unit Retail Price');
        $spreadSheet->getActiveSheet()->SetCellValue('X1', 'USD Tester Price');
        $spreadSheet->getActiveSheet()->SetCellValue('Y1', 'Image 1');
        $spreadSheet->getActiveSheet()->SetCellValue('Z1', 'Image 2');
        $spreadSheet->getActiveSheet()->SetCellValue('AA1', 'Image 3');
        $spreadSheet->getActiveSheet()->SetCellValue('AB1', 'Image 4');
        $spreadSheet->getActiveSheet()->SetCellValue('AC1', 'Fabric Content');
        $spreadSheet->getActiveSheet()->SetCellValue('AD1', 'Care Instructions');
        $spreadSheet->getActiveSheet()->SetCellValue('AE1', 'Season');
        $spreadSheet->getActiveSheet()->SetCellValue('AF1', 'Occasion');
        $spreadSheet->getActiveSheet()->SetCellValue('AG1', 'Aesthetic');
        $spreadSheet->getActiveSheet()->SetCellValue('AH1', 'Fit');
        $spreadSheet->getActiveSheet()->SetCellValue('AI1', 'Secondary Occasion');
        $spreadSheet->getActiveSheet()->SetCellValue('AJ1', 'Secondary Aesthetic');
        $spreadSheet->getActiveSheet()->SetCellValue('AK1', 'Secondary Fit');
        $spreadSheet->getActiveSheet()->SetCellValue('AL1', 'Preorder');
        $spreadSheet->getActiveSheet()->SetCellValue('AM1', 'Ship By Date (YYYY-MM-DD)');
        $spreadSheet->getActiveSheet()->SetCellValue('AN1', 'Ship By End Date (if range, YYYY-MM-DD)');
        $spreadSheet->getActiveSheet()->SetCellValue('AO1', 'Order By Date (YYYY-MM-DD)');

        $this->cellColor("A1:H1", 'b6d7a8', $spreadSheet);
        $this->cellColor("I1:W1", 'A8C3C8', $spreadSheet);
        $this->cellColor("X1", 'b6d7a8', $spreadSheet);
        $this->cellColor("Y1:AB1", 'E1E599', $spreadSheet);
        $this->cellColor("AC1:AK1", 'D5A6BD', $spreadSheet);
        $this->cellColor("AL1:AO1", '9FC5E8', $spreadSheet);


        $spreadSheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $spreadSheet->getActiveSheet()->SetCellValue('A2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('B2', 'Optional, defaults to Published');
        $spreadSheet->getActiveSheet()->SetCellValue('C2', 'Optional - Faire will add if left blank');
        $spreadSheet->getActiveSheet()->SetCellValue('D2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('E2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('F2', 'Mandatory for Case Pack. Leave blank for Open Sizing.');
        $spreadSheet->getActiveSheet()->SetCellValue('G2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('H2', 'Optional, defaults to Case Pack');
        $spreadSheet->getActiveSheet()->SetCellValue('I2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('J2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('K2', 'Mandatory if Option 1 Name is filled');
        $spreadSheet->getActiveSheet()->SetCellValue('L2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('M2', 'Mandatory if Option 2 Name is filled');
        $spreadSheet->getActiveSheet()->SetCellValue('N2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('O2', 'Mandatory if Option 3 Name is filled');
        $spreadSheet->getActiveSheet()->SetCellValue('P2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('Q2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('R2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('S2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('T2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('U2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('V2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('W2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('X2', 'Optional, defaults to blank which means there is not a tester');
        $spreadSheet->getActiveSheet()->SetCellValue('Y2', 'Mandatory for at least one row for each product');
        $spreadSheet->getActiveSheet()->SetCellValue('Z2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('AA2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('AB2', 'Mandatory');
        $spreadSheet->getActiveSheet()->SetCellValue('AC2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AD2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AE2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AF2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AG2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AH2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AI2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AJ2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AK2', 'Optional, only for apparel');
        $spreadSheet->getActiveSheet()->SetCellValue('AL2', 'Optional');
        $spreadSheet->getActiveSheet()->SetCellValue('AM2', 'Mandatory if Preorder');
        $spreadSheet->getActiveSheet()->SetCellValue('AN2', 'Optional if Preorder');
        $spreadSheet->getActiveSheet()->SetCellValue('AO2', 'Optional if Preorder');
        $this->cellColorSecond("A2:H2", 'D9EAD3', $spreadSheet);
        $this->cellColorSecond("I2:W2", 'D3DFE2', $spreadSheet);
        $this->cellColorSecond("X2", 'D9EAD3', $spreadSheet);
        $this->cellColorSecond("Y2:AB2", 'FFF2CC', $spreadSheet);
        $this->cellColorSecond("AC2:AK2", 'EAD1DC', $spreadSheet);
        $this->cellColorSecond("AL2:AO2", 'CFE2F3', $spreadSheet);
        $spreadSheet->getActiveSheet()->setPrintGridlines(true);
        $spreadSheet->getActiveSheet()->getRowDimension('2')->setRowHeight(30);
        $rowCount = 3;
        foreach ($products as $v) {
            $qryCategory = Category::where("id", $v->category)->first();

            if (empty($qryCategory)) {
                $category = '';
            } else {
                $category = $qryCategory->name;
            }


            $getImage = ProductImage::where("product_id", $v->id)->get();


            $getCountry = Country::where("id", $v->country)->first();
            if (!empty($getCountry)) {
                $country = $getCountry->name;
            } else {
                $country = '';
            }


            $spreadSheet->getActiveSheet()->SetCellValue('A' . $rowCount, $v->name);
            $spreadSheet->getActiveSheet()->SetCellValue('B' . $rowCount, $v->status);
            $spreadSheet->getActiveSheet()->SetCellValue('C' . $rowCount, $category);
            if ($v->description == 'undefined') {
                $spreadSheet->getActiveSheet()->SetCellValue('D' . $rowCount, '');
            } else {
                $spreadSheet->getActiveSheet()->SetCellValue('D' . $rowCount, $v->description);
            }
            $spreadSheet->getActiveSheet()->SetCellValue('E' . $rowCount, $country);
            $spreadSheet->getActiveSheet()->SetCellValue('F' . $rowCount, $v->case_quantity);
            $spreadSheet->getActiveSheet()->SetCellValue('G' . $rowCount, $v->min_order_qty);
            $spreadSheet->getActiveSheet()->SetCellValue('H' . $rowCount, $v->min_order_qty_type);
            $spreadSheet->getActiveSheet()->SetCellValue('I' . $rowCount, $v->sku);
            $spreadSheet->getActiveSheet()->SetCellValue('J' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('K' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('L' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('M' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('N' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('O' . $rowCount, '');
            $spreadSheet->getActiveSheet()->SetCellValue('P' . $rowCount, $v->usd_wholesale_price);
            $spreadSheet->getActiveSheet()->SetCellValue('Q' . $rowCount, $v->usd_retail_price);
            $spreadSheet->getActiveSheet()->SetCellValue('R' . $rowCount, $v->cad_wholesale_price);
            $spreadSheet->getActiveSheet()->SetCellValue('S' . $rowCount, $v->cad_retail_price);
            $spreadSheet->getActiveSheet()->SetCellValue('T' . $rowCount, $v->gbr_wholesale_price);
            $spreadSheet->getActiveSheet()->SetCellValue('U' . $rowCount, $v->gbr_retail_price);
            $spreadSheet->getActiveSheet()->SetCellValue('V' . $rowCount, $v->eur_wholesale_price);
            $spreadSheet->getActiveSheet()->SetCellValue('W' . $rowCount, $v->eur_retail_price);
            $spreadSheet->getActiveSheet()->SetCellValue('X' . $rowCount, $v->usd_tester_price);
            if (!empty($getImage)) {
                $image = 1;
                foreach ($getImage as $key => $var) {
                    if ($image == 1) {
                        $spreadSheet->getActiveSheet()->SetCellValue('Y' . $rowCount, $var->images);
                    }
                    if ($image == 2) {
                        $spreadSheet->getActiveSheet()->SetCellValue('Z' . $rowCount, $var->images);
                    }
                    if ($image == 3) {
                        $spreadSheet->getActiveSheet()->SetCellValue('AA' . $rowCount, $var->images);
                    }
                    if ($image == 4) {
                        $spreadSheet->getActiveSheet()->SetCellValue('AB' . $rowCount, $var->images);
                    }
                    $image++;
                }
            }

            $spreadSheet->getActiveSheet()->SetCellValue('AC' . $rowCount, $v->fabric_content);
            $spreadSheet->getActiveSheet()->SetCellValue('AD' . $rowCount, $v->care_instruction);
            $spreadSheet->getActiveSheet()->SetCellValue('AE' . $rowCount, $v->season);
            $spreadSheet->getActiveSheet()->SetCellValue('AF' . $rowCount, $v->Occasion);
            $spreadSheet->getActiveSheet()->SetCellValue('AG' . $rowCount, $v->Aesthetic);
            $spreadSheet->getActiveSheet()->SetCellValue('AH' . $rowCount, $v->Fit);
            $spreadSheet->getActiveSheet()->SetCellValue('AI' . $rowCount, $v->Secondary_Occasion);
            $spreadSheet->getActiveSheet()->SetCellValue('AJ' . $rowCount, $v->Secondary_Aesthetic);
            $spreadSheet->getActiveSheet()->SetCellValue('AK' . $rowCount, $v->Secondary_Fit);
            $spreadSheet->getActiveSheet()->SetCellValue('AL' . $rowCount, $v->Preorder);
            $spreadSheet->getActiveSheet()->SetCellValue('AM' . $rowCount, $v->product_shipdate);
            $spreadSheet->getActiveSheet()->SetCellValue('AN' . $rowCount, $v->product_endshipdate);
            $spreadSheet->getActiveSheet()->SetCellValue('AO' . $rowCount, $v->product_deadline);
            $rowCount++;
        }

        foreach (range('A', 'Z') as $column) {
            $spreadSheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(false);
            $spreadSheet->getActiveSheet()->getColumnDimension($column)->setWidth('30');
        }
        $spreadSheet->getActiveSheet()->getColumnDimension('AA')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AA')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AB')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AB')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AC')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AC')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AD')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AD')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AE')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AE')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AF')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AF')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AG')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AG')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AH')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AH')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AI')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AI')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AJ')->setWidth('30');


        $spreadSheet->getActiveSheet()->getColumnDimension('AK')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AK')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AL')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AL')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AM')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AM')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AN')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AN')->setWidth('30');

        $spreadSheet->getActiveSheet()->getColumnDimension('AO')->setAutoSize(false);
        $spreadSheet->getActiveSheet()->getColumnDimension('AO')->setWidth('30');

        $fileName = rand() . ".xlsx";
        $folderPath = $this->absPath . $fileName;
        $objWriter = IOFactory::createWriter($spreadSheet, 'Xlsx');
        $objWriter->save($folderPath);
        $file = $this->realPath . $fileName;
        $response = ['res' => true, 'msg' => "", 'data' => $file];
        return response()->json($response);


    }


}
