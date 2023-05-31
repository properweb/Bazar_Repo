<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class CreateCustomerRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
            'store_name' => 'required|string|max:255',
            'shipping_name' => 'required|string|max:255',
            'shipping_country' => 'required|integer|exists:countries,id',
            'shipping_street' => 'required|string|max:255',
            'shipping_suite' => 'nullable|string|max:255',
            'shipping_state'=> 'required|integer|exists:states,id',
            'shipping_town' => 'required|integer|exists:cities,id',
            'shipping_zip' => 'required|string|max:255',
            'shipping_phone_code' => 'required|numeric',
            'shipping_phone' => 'required|numeric|digits:10',
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
