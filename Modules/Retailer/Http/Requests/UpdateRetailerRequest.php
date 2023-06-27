<?php

namespace Modules\Retailer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class UpdateRetailerRequest extends FormRequest
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
            'first_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'last_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'store_name' => 'required|string|max:255',
            'country_code' => 'required|numeric',
            'country' => 'required|integer|exists:countries,id',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'language' => 'required|string',
            'store_type' => 'required|string',
            'website_url' => ['required','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/'],
            'old_password' => 'sometimes',
            'new_password' => [
                'sometimes',
                'different:old_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'confirm_password' => 'sometimes|same:new_password'
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
