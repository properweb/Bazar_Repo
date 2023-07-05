<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Http\Request;
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
            'user_id' => 'required|integer|exists:users,id',
            'city' => 'required|integer|exists:cities,id',
            'state' => 'required|integer|exists:states,id',
            'cover_image' => 'required|string',
            'established_year' => 'required|digits:4|integer|min:1900|max:'.date('Y'),
            'featured_image' => 'required|string',
            'headquatered' => 'required|integer|exists:countries,id',
            'insta_handle' => ['required','regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,29}$/'],
            'logo_image' => 'required|string',
            'product_made' => 'required|integer|exists:countries,id',
            'profile_photo' => 'required|string',
            'publications' => 'nullable|string|max:255',
            'shared_brd_story' => 'required|string|max:1500',
            'tag_shop_page' => 'required|string|max:255',
            'tag_shop_page_about' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url',
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
        //dd($validator->errors());
        throw new HttpResponseException(response()->json([
            'res' => false,
            'msg' => $validator->errors()->first(),
            'data' => ""
        ]));

    }
}
