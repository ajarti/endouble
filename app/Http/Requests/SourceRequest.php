<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SourceRequest extends FormRequest
{

    /**
     * Merge Route Params into request.
     *
     * @return array
     */
    public function all($keys = null)
    {
        return array_replace_recursive(
            parent::all($keys),
            $this->route()->parameters()
        );
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'limit'  => 'integer',
            'offset' => 'integer',
            'source' => 'required|exists:sources,slug',
            'year'   => 'integer'
        ];
    }


    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'source.required' => 'A source is required.',
            'source.exists'   => 'The specified source does not exist.',
        ];
    }


    /**
     * Failed validation disable redirect
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = [
            'meta'   => [
                'request'   => [
                    'source' => $this->source,
                    'year'   => $this->year,
                    'limit'  => $this->limit,
                ],
                'timestamp' => \Carbon\Carbon::now()
            ],
            'errors' => $validator->errors()
        ];


        throw new HttpResponseException(response()->json($response, 422));
    }

}
