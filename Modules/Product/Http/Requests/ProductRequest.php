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
            'shipping_sku' => ['nullable', 'regex:/(^[-0-9A-Za-z-\/ ]+$)/','max:60'],
            'order_case_qty' => 'required|integer',
            'usd_wholesale_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'usd_retail_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'cad_wholesale_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'cad_retail_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'gbp_wholesale_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'gbp_retail_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'aud_wholesale_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'aud_retail_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'eur_wholesale_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'eur_retail_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'testers_price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'retailer_add_charge' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
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
