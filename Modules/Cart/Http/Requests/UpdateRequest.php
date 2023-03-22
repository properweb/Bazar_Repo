<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class UpdateRequest extends FormRequest
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
            'cart' => 'required|array',
            'cart.*' => 'required|array',
            'cart.*.id' => 'required|integer',
            'cart.*.product_id' => 'required|integer',
            'cart.*.product_image' => 'nullable|string',
            'cart.*.product_name' => 'required|string|max:60',
            'cart.*.product_price' => 'required|regex:/^\d{0,9}(\.\d{0,2})?$/',
            'cart.*.product_qty' => 'required|integer',
            'cart.*.user_id' => 'required|integer',

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
