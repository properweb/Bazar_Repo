<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class UpdateBrandRequest extends FormRequest
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
            'brand_name' => 'nullable|string|max:255',
            'prime_cat' => 'nullable|integer|exists:categories,id',
            'website_url' => ['nullable','regex:/^(?!(http|https)\.)\w+(\.\w+)+$/'],
            'country_code' => 'nullable|numeric',
            'country' => 'nullable|integer|exists:countries,id',
            'phone_number' => 'nullable|numeric|digits:10',
            'headquatered' => 'nullable|integer|exists:countries,id',
            'city' => 'nullable|integer|exists:cities,id',
            'state' => 'nullable|integer|exists:states,id',
            'product_made' => 'nullable|integer|exists:countries,id',
            'product_shipped' => 'nullable|integer|exists:countries,id',
            'avg_lead_time' => 'nullable|numeric|min:1|max:180',
            'shop_lead_time' => 'nullable|numeric|min:1|max:180',
            'established_year' => 'nullable|digits:4|integer|min:1900|max:'.date('Y'),
            'insta_handle' => ['nullable','regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,29}$/'],
            'shared_brd_story' => 'nullable|string|max:1500',
            'tag_shop_page_about' => 'nullable|string|max:1000',
            'photo_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'photo_lib_link' => 'nullable|url',
            'first_order_min' => 'nullable|numeric|min:1|max:99999',
            're_order_min' => 'nullable|numeric|min:1|max:99999',
            'scheduled_date' => 'nullable|date_format:Y-m-d',
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
