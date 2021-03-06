<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AdminOrderDeleteRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $errorBag = 'delete';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator $validator
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');

            if ($order->status === 'completed') {
                $validator->errors()->add('order', 'Could not delete the order because it is marked as completed');
            }
        });
    }
}
