<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    //
    public function pruebas(Request $request){
    	return "Accion de pruebas de UserController";

    }
    public function register(Request $request){

    	//Recoger lo datos del usuario por post
    	$json =$request->input('json',null);
    	//var_dump($json);
    	//die();
    	$params = json_decode($json);//objeto
    	$params_array = json_decode($json,true);//array
    	//var_dump($params_array); die();
    	if(!empty($params) && !empty($params_array)){
    	//limpiar datos 
    	$params_array =array_map('trim',$params_array);

    	//validar datos
    	$validate = \Validator::make($params_array,[
    	'name'   => 'required|alpha ',
    	'surname'=> 'required|alpha ',
    	//comporbar is el usuario exite ya(duplicado)
    	'email'  => 'required|email|unique:users',
    	'password'=> 'required'
    	]);
    	if($validate->fails()){
    		//La validacion ha pasado
    		$data =array(
    		'status' =>'error',
    		'code' =>404,
    		'message' =>'El usuario no se ha creado',
    		'errors' => $validate->errors()
    	);

    	}else{
    	//Validción pasada correctamente 

    	//cifrar la contraseña
    	$pwd = hash('sha256', $params->password);

    	//crear el usuario
    	$user =new User();
    	$user->name =$params_array['name'];
    	$user->surname=$params_array['surname'];
    	$user->email =$params_array['email'];
    	$user->password =$pwd;
    	$user->role ='ROLE_USER';
    	//Guardar el usuario
    	$user->save(); 

    		$data =array(
    		'status' =>'success',
    		'code' =>200,
    		'message' =>'El usuario  se ha creado',
    		'user' => $user
    		
    	);

    	}

    }else{
    	$data =array(
    		'status' =>'error',
    		'code' =>404,
    		'message' =>'Los datos enviados no son correctos',
    		
    	);

    }
    
    	return response()->json($data,$data['code']);

    }
    public function login(Request $request){
        $JwtAuth =new \App\Helpers\JwtAuth();
        //Recibir datos por Post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);
        //validar esos datos 
        $validate = \Validator::make($params_array,[
    
        //comporbar is el usuario exite ya(duplicado)
        'email'  => 'required|email',
        'password'=> 'required'
        ]);
        if($validate->fails()){
            //La validacion ha pasado
            $signup =array(
            'status' =>'error',
            'code' =>404,
            'message' =>'El usuario no se ha podido idetificar',
            'errors' => $validate->errors()
        );
        }else {

        //cofrar la password
         $pwd = hash('sha256', $params->password);
        //devolver token o datos
         $signup =$JwtAuth->signup($params->email,$pwd);

         if(!empty($params->gettoken)){
            $signup =$JwtAuth->signup($params->email,$pwd,true);

         }
        }
 return response()->json($signup, 200);
    }
    public function update(Request $request){

        //Comprobar si el usuario ya esta identificado
        $token = $request->header('Authorization');
        $JwtAuth =new \App\Helpers\JwtAuth();
        $checkToken =$JwtAuth->checkToken($token);

          //Recoger los datos por post 
            $json = $request->input('json',null);
            $params_array =json_decode($json,true);

        if ($checkToken && !empty($params_array)) {

            //Sacar usuario identificado
             $user =$JwtAuth->checkToken($token, true);



            //validar datos
            $validate = \Validator::make($params_array,[

        'name'   => 'required|alpha ',
        'surname'=> 'required|alpha ',
        //comporbar is el usuario exite ya(duplicado)
        'email' => 'required|email|unique:users,email,'.$user->sub
            ]);

            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar los daros en bbdd
            $user_update =User::where('id', $user->sub)->update($params_array);

            //Devolver array con resultado
            $data =array(
                 'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' =>   $params_array
            );

        }else{
              $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function upload(Request $request){
        ///Recoger los datos de la peticion
        $image =$request->file('file0');
        //validacion de la imagen 
        $validate = \Validator::make($request->all(),[
            'file0'=> 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Subir y guardar la imagen
        if(!$image || $validate->fails()){
                $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen '
            );

        }else{

            $image_name =time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = array(
                'code' =>200,
                'status' => 'success',
                'image' =>$image_name

            );
         
        }

        //Devolver el resultao 
         return response()->json($data, $data['code']);

    }
    public function getImage($filename){

        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
        
       
        $file =\Storage::disk('users')->get($filename);
        return new Response($file,200);
    }else{
          $data = array(
                'code' =>404,
                'status' => 'error',
                'message' =>'La imagen no existe.'

            );
          return response()->json($data, $data['code']);

    }
 
}
public function detail($id){
    $user =User::find($id);
    if (is_object($user)) {
        $data =array(
            'code' =>200,
            'status' => 'success',
            'user' => $user 
        );
    }else{
         $data =array(
            'code' =>404,
            'status' => 'error',
            'message' => 'El usuario no existe.' 
        );

    }
     return response()->json($data, $data['code']);


 }
}
