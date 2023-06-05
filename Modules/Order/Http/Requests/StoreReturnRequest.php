<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class StoreReturnRequest extends FormRequest
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
            'policy_values' => 'required|array',
            'feedback' => 'string',
            'order_id' => 'integer|exists:orders,id',
            'products' => 'required|array',
            'shipping_time' => 'required',
            'order_number' => 'required',
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
