<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class AcceptRequest extends FormRequest
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

            'brand_address1' => 'required|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'brand_address2' => 'nullable|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'brand_phone' => ['required', 'digits:10'],
            'brand_post_code' => 'nullable|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'ord_no' => 'required',
            'brand_country' => 'required|integer',
            'brand_name' => ['required', 'regex:/^[\p{L}\s-]+$/u','max:255'],
            'brand_state' => 'required|integer',
            'brand_town' => 'required|integer',
            'ship_date' => 'nullable',
            'user_id' => 'required|integer'

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
