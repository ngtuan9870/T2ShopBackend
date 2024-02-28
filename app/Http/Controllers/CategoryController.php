<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function add(Request $request){
        $validate = Validator::make($request->all(),
            [
                'category_name'=>'unique:categories,category_name'
            ],
            [
                'category_name.unique'=>'Tên thể loại không được trùng'
            ],
        );
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()->all()]);
        }
        $c = new Category();
        $c->category_name = $request->category_name;
        $c->save();
        return response()->json(['success'=>"Thêm thể loại thành công!"]);
    }
    public function show(){
        $categories = DB::table('categories')->select('*')->get();
        return response()->json(['categories'=>$categories]);
    }
    public function getedit(Request $request){
        $category = DB::table('categories')->select('*')->where('category_id','=',$request->id)->first();
        return response()->json($category);
    }
    public function delete($id){
        Category::destroy($id);
    }
    public function postedit(Request $request){
        $category = DB::table('categories')->select('*')->where('category_id','=',$request->category_id)->first();
        $c = Category::find($request->category_id);
        $validate = Validator::make($request->all(),
            [
                'category_name'=>'required|unique:categories,category_name'
            ],
            [
                'category_name.required'=>'Tên thương hiệu không được trùng',
                'category_name.unique'=>'Tên thương hiệu bị trùng'
            ]
        );
        if($validate->fails()){
            if($request->category_name==$c->category_name){
                return response()->json(['success'=>'same']);
            }
            return response()->json(['error'=>'error']);
        }
        $c->category_name = $request->category_name;
        $c->save();
        return response()->json(['success'=>'success']);
    }
    public function checkname(Request $request){
        $validate = Validator::make($request->all(),
            [
                'category_name'=>'unique:categories,category_name'
            ],
            [
                'category_name.unique'=>'Tên thể loại không được trùng'
            ],
        );
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()->all()],409);
        }
        return response()->json(['success'=>'Không bị trùng']);
    }
    public function remove(Request $request){
        $c = Category::find($request->category_id);
        $c->delete();
    }
}
