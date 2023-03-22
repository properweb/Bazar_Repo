<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class UpdateBrandInfoRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'avg_lead_time' => 'required|numeric|min:1|max:180',
            'brand_name' => 'required|string|max:255',
            'established_year' => 'required|digits:4|integer|min:1900|max:'.date('Y'),
            'first_order_min' => 'required|numeric|min:1|max:99999',
            'headquatered' => 'required|integer|exists:countries,id',
            'insta_handle' => ['required','regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,29}$/'],
            'product_made' => 'required|integer|exists:countries,id',
            'profile_photo' => 'required|string',
            're_order_min' => 'required|numeric|min:1|max:99999',
            'shared_brd_story' => 'required|string|max:1500',
            'stored_carried' => 'required|string|max:255',
            'tag_shop_page' => 'required|string|max:255',
            'website_url' => ['required','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/'],
            'upload_contact_list' => 'nullable|string|max:255',
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
