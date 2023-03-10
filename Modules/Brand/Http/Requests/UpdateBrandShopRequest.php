<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class UpdateBrandShopRequest extends FormRequest
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
            'email' => 'string|email|unique:users,email,' . $this->user->id . ',id',
            'brand_slug' => 'string|unique:brands,brand_slug,' . $this->brand->id . ',id',
            'brand_name' => 'string|max:255',
            'website_url' => ['regex:/^(?!(http|https)\.)\w+(\.\w+)+$/'],
            'insta_handle' => ['regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,29}$/'],
            'established_year' => 'digits:4|integer|min:1900|max:' . date('Y'),
            'first_order_min' => 'numeric|min:1|max:99999',
            're_order_min' => 'numeric|min:1|max:99999',
            'avg_lead_time' => 'numeric|min:1|max:180',
            'product_made' => 'integer|exists:countries,id',
            'headquatered' => 'integer|exists:countries,id',
            'shared_brd_story' => 'string|max:1500',
            'tag_shop_page' => 'string|max:1500',
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
