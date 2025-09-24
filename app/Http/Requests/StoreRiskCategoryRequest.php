<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreRiskCategoryRequest extends FormRequest {
  public function authorize(){ return true; }
  public function rules(){ return ['name'=>'required|string|max:120','parent_id'=>'nullable|exists:risk_categories,id']; }
}
