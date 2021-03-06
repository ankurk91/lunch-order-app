<?php

namespace App\Http\Requests\Product;

use Illuminate\Support\Str;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
            $product = $this->route('product');

            $productUsageCount = Order::withTrashed()
                ->whereHas('orderProducts', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })->count();

            if ($productUsageCount) {
                $validator->errors()->add('product', 'Could not delete the product because it is already being used in ' . $productUsageCount . ' ' . Str::plural('order', $productUsageCount) . '.');
            }
        });
    }
}
