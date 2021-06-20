<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller{
 public function __construct() {
 $this->middleware('api.auth',['except' => ['index','show']]);

}

   public function index(){
   	$categories = Category::all();
   	return response()->json([
   		'code' =>200,
   		'status' =>'success',
   		'categories' =>$categories
   	]);
    }
    public function show($id){
    	$category =Category::find($id);
    	if(is_object($category)){
    		$data =[
	    'code' =>200,
   		'status' =>'success',
   		'categories' =>$category
   	];
    	}else{
    	$data =[
	    'code' =>404,
   		'status' =>'error',
   		'message' =>'La categoria no existe'
   	];

    	}
    	    return response()->json($data,$data['code']);
    }
    //gurdar una ategoria 
    public function store(Request $request){
    	//Recoger los dato por post
    	$json =$request->input('json',null);
    	$params_array =json_decode($json,true);
    	if(!empty($params_array)){

   
    	//validar los datos
    	$validate =\Validator::make($params_array,[
    		'name'=> 'required'
    	]);


    	//guardar la categoria
    	if ($validate->fails()) {
    		$data =[
	    'code' =>400,
   		'status' =>'error',
   		'message' =>'NO se ha guardado la categoria.'
   	];
    	}else{
    		$category =new Category();
    		$category->name = $params_array['name'];
    		$category->save();
    		$data =[
	    'code' =>200,
   		'status' =>'success',
   		'message' =>$category
   	];
    	}
    		}else{
    				$data =[
	    'code' =>400,
   		'status' =>'error',
   		'message' =>'NO has enviado ninguna categoria'
   	];

    		}

    	//devolver resutado
    	return response()->json($data,$data['code']);


}
//Actualizar la categoria
public function update($id,Request $request){
	//Recoger datos por post
	$json =$request->input('json',null);
	$params_array = json_decode($json,true); 
	if(!empty($params_array)){
	//validar los datos 
		$validate = \Validator::make($params_array,[
			'name' =>'required'
		]);

	//Quitar lo que no quiero actualizar unsey quita una variable

	unset($params_array['id']);
	unset($params_array['created_at']); 

	//Actualizar el registro (categoria)
	$category = Category::where('id',$id)->update($params_array);
	$data =[
	    'code' =>200,
   		'status' =>'success',
   		'category' =>$params_array
   	];


	}else{
		$data =[
	    'code' =>400,
   		'status' =>'error',
   		'message' =>'NO has enviado ninguna categoria.'
   	];


	}

	//devolver la respuesta
    return response()->json($data,$data['code']);

}

}