<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class StoreShippingRequest extends FormRequest
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
            'name' => ['required', 'regex:/^[\p{L}\s-]+$/u', 'max:255'],
            'country' => 'required|integer',
            'id' => 'nullable|integer',
            'street' => 'required|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'suite' => 'nullable|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'state' => 'required|integer',
            'town' => 'required|integer',
            'zip' => ['required', 'max:10'],
            'phoneCode' => 'required|integer',
            'phone' => ['required', 'digits:10']

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
