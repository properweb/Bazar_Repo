<?php

namespace Modules\Product\Http\Requests;

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
        return [
            'product_name' => 'required|string|max:60',
            'user_id' => 'required|integer',
            'product_made' => 'required|integer',
            'usd_wholesale_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'usd_retail_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'cad_wholesale_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'cad_retail_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'gbp_wholesale_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'gbp_retail_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'aud_wholesale_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'aud_retail_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/|',
            'eur_wholesale_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/|',
            'eur_retail_price' => 'required|regex:/^\d{0,6}(\.\d{0,2})?$/|',
            'shipping_sku' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:10'],
            'shipping_inventory' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'shipping_tariff_code' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'shipping_length' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'shipping_width' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'shipping_height' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'shipping_weight' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:6'],
            'order_case_qty' => 'required|regex:/(^[-0-9A-Za-z-\/ ]+$)/|max:6',
            'testers_price' => 'nullable|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'reatailer_input_limit' => 'nullable|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'retailer_add_charge' => 'nullable|regex:/^\d{0,6}(\.\d{0,2})?$/',
            'product_images' => 'array|min:1'
        ];
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
