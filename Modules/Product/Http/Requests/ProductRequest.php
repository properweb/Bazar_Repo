<?php

namespace Modules\Product\Http\Requests;

use Modules\Product\Rules\CheckOptionDuplicates;
use Modules\Product\Rules\CheckPrePack;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\JsonResponse;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        $priceOptionRules = 'required_if:options_available,1|regex:/^\d{0,9}(\.\d{0,2})?$/';
        $priceRules = 'required_if:options_available,0|regex:/^\d{0,9}(\.\d{0,2})?$/';

        return [
            'product_name' => 'required|string|max:60',
            'product_made' => 'required|integer',
            'usd_wholesale_price' => $priceRules,
            'usd_retail_price' => $priceRules,
            'cad_wholesale_price' => $priceRules,
            'cad_retail_price' => $priceRules,
            'gbp_wholesale_price' => $priceRules,
            'gbp_retail_price' => $priceRules,
            'aud_wholesale_price' => $priceRules,
            'aud_retail_price' => $priceRules,
            'eur_wholesale_price' => $priceRules,
            'eur_retail_price' => $priceRules,
            'shipping_sku' => 'nullable:options_available,0|regex:/^[a-zA-Z0-9]*$/',
            'shipping_inventory' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'shipping_tariff_code' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'shipping_length' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'shipping_width' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'shipping_height' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'shipping_weight' => 'nullable:options_available,0|regex:/^[0-9]{0,6}$/',
            'product_images' => 'required_if:fromAdd,1|array|min:1',
            'variations' => ['required_if:options_available,1|array', new CheckOptionDuplicates],
            'variations.*' => 'required_if:options_available,1|array',
            'variations.*.usd_wholesale_price' => $priceOptionRules,
            'variations.*.usd_retail_price' => $priceOptionRules,
            'variations.*.cad_wholesale_price' => $priceOptionRules,
            'variations.*.cad_retail_price' => $priceOptionRules,
            'variations.*.gbp_wholesale_price' => $priceOptionRules,
            'variations.*.gbp_retail_price' => $priceOptionRules,
            'variations.*.aud_wholesale_price' => $priceOptionRules,
            'variations.*.aud_retail_price' => $priceOptionRules,
            'variations.*.eur_wholesale_price' => $priceOptionRules,
            'variations.*.eur_retail_price' => $priceOptionRules,
            'variations.*.sku' => 'nullable:options_available,1|regex:/^[a-zA-Z0-9]*$/',
            'variations.*.inventory' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'variations.*.weight' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'variations.*.length' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'variations.*.width' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'variations.*.height' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'variations.*.tariff_code' => 'nullable:options_available,1|regex:/^[0-9]{0,6}$/',
            'order_case_qty' => 'required|regex:/^[0-9]{1,6}$/',
            'testers_price' =>  "required_if:retailersPrice,1|nullable|regex:/^\d{0,9}(\.\d{0,2})?$/",
            'reatailers_inst' => "required_if:instructionsRetailers,1|nullable|string|max:70",
            'reatailer_input_limit' => "required_if:instructionsRetailers,1|nullable|integer|max:6",
            'retailer_add_charge' => "nullable:instructionsRetailers,1|nullable|regex:/^\d{0,9}(\.\d{0,2})?$/",
            'retailer_min_qty' => "required_if:instructionsRetailers ,1|nullable|integer",
            'pre_packs' => ['required_if:prepackAvailable,1|array', new CheckPrePack],
            'product_shipdate' => 'required_if:retailersPreOrderDate,1|nullable',
            'product_endshipdate' => 'required_if:retailersPreOrderDate,1|nullable',
        ];
    }

    /**
     * Variations function created
     *
     * @return mixed
     */
    public function getValidatorInstance()
    {
        $this->formatVariations();
        if(!empty($this->request->get('pre_packs'))) {
            $this->formatPack();
        }

        return parent::getValidatorInstance();
    }

    /**
     * Variations convert to array
     *
     * @return void
     */

    protected function formatVariations()
    {

        $this->request->set(
            'variations',json_decode($this->request->get('variations'), true)
        );
    }



    /**
     * Pre pack convert to array
     *
     * @return void
     */
    protected function formatPack()
    {
        $this->request->set(
            'pre_packs',json_decode($this->request->get('pre_packs'), true)
        );

    }

    /**
     * Create a json response on validation errors.
     *
     * @param Validator $validator
     * @return JsonResponse
     */
    public function failedValidation(Validator $validator): JsonResponse
    {
        throw new HttpResponseException(response()->json([
            'res' => false,
            'msg' => $validator->errors()->first(),
            'data' => ""
        ]));

    }
}
