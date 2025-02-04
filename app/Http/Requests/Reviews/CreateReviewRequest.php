<?php

namespace App\Http\Requests\Reviews;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'book_id' => 'required|exists:books,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'book_id.required' => 'Sách là bắt buộc.',
            'book_id.exists' => 'Sách không hợp lệ.',
            'rating.required' => 'Đánh giá là bắt buộc.',
            'rating.integer' => 'Đánh giá phải là một số nguyên.',
            'rating.min' => 'Đánh giá phải tối thiểu là 1.',
            'rating.max' => 'Đánh giá không được vượt quá 5.',
            'comment.required' => 'Nhận xét là bắt buộc.',
            'comment.min' => 'Nhận xét phải có ít nhất 5 ký tự.',
            'comment.max' => 'Nhận xét không được dài quá 1000 ký tự.',
        ];
    }
}