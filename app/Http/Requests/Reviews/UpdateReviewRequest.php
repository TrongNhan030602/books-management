<?php

namespace App\Http\Requests\Reviews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:5|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'rating.integer' => 'Đánh giá phải là một số nguyên.',
            'rating.min' => 'Đánh giá phải tối thiểu là 1.',
            'rating.max' => 'Đánh giá không được vượt quá 5.',
            'comment.min' => 'Nhận xét phải có ít nhất 5 ký tự.',
            'comment.max' => 'Nhận xét không được dài quá 1000 ký tự.',
        ];
    }
}