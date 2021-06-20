<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    
 public function __construct() {
    $this->middleware('api.auth',['except' => ['index','show','getImage','getPostByCategory','getPostsByUser']]);

    }
    public function index(){
        $posts =Post::all()->load('category');

        return response()->json([
            'code' =>200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

   public function show($id){
    $post = Post::find($id)->load('category')
                           ->load('user');
    if(is_object($post)){
        $data = [
            'code' =>200,
            'status' => 'success',
            'posts' => $post
        ];

    }else{
        $data = [
            'code' =>404,
            'status' => 'error',
            'message' => 'La ntrada no existe'
        ];

    }
    return response()->json($data, $data['code']);
   } 
   public function store(Request $request){
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
 
        if(!empty($params_array)){
            // Conseguir usuario identificado
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);
 
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'title'         =>  'required',
                'content'       =>  'required',
                'category_id'   =>  'required',
                'image'         =>  'required'
            ]); //los required son datos obligatorios
 
                if($validate->fails()){
                    $data = [
                        'code'      =>  400,
                        'status'    =>  'error',
                        'message'   =>  'No se ha guardado el post, faltan datos'
                    ];
                }else{
                    // Guardar el articulo
                    $post = new Post();
                    $post->user_id      = $user->sub;
                    $post->category_id  = $params->category_id;
                    $post->title        = $params->title;
                    $post->content      = $params->content;
                    $post->image        = $params->image;
                    $post->save(); //y esto hace un insert en la base de datos
 
                    $data = [
                        'code'      =>  200,
                        'status'    =>  'success',
                        'post'      =>  $post
                    ];
                }
 
        }else{
            $data = [
                'code'      =>  400,
                'status'    =>  'error',
                'message'   =>  'Envia los datos correctamente'
            ];
 
        }
        //Devolver respuesta
        return response()->json($data, $data['code']);
    }



    /*

    */
 public function update($id, Request $request){
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
 
 
        
            //Devolver algo
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Datos enviados incorrectos'
            );
 
 
        if(!empty($params_array)){
        //Validar datos
        $validate = \Validator::make($params_array,[
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
         ]);
 
 
         if($validate->fails()){
             $data['errors'] = $validate->errors();
 
            return response()->json($validate->errors(), 400);
         }
 
        //Eliminar lo que no queremos actualizar
        unset($params_array['id']);
        unset($params_array['user_id']);
        unset($params_array['created_at']);
        unset($params_array['user']);
 
        //conseguir usuario identificado
        $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);
        
        
        //Buscar el registro para actualizar
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
                                
 
        if(!empty($post) && is_object($post)){
 
            //Actualizar el registro en concreto
            $post->update($params_array);
 
            //Devolver algo
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post,
                'changes' => $params_array,
 
            );
        }
        
                          
 
        
          }
        return response()->json($data, $data['code']);
 
    }
   public function destroy($id,Request $request){
    //conseguir usuario
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

    //conseguir el post
    $post = Post::where('id',$id)
                ->where('user_id',$user->sub)
                ->first();

    if(!empty($post)){

    //borrarlo
    $post->delete();


    //devolver algo
    $data = [
        'code' => 200,
        'status' => 'success',
        'post' => $post
    ];

}else{
     $data = [
        'code' => 404,
        'status' => 'error',
        'message' => 'El post no existe'
    ];

}
    return response()->json($data, $data['code']);

   }
   public function upload(Request $request){
    //recoger la imagen de la peticion
    $image = $request->file('file0');

    //validar la imagen 
    $validate =\Validator::make($request->all(),[
        'file0' =>'required|mimes:jpg,jpeg,png,gif'

    ]);

    //guarar la imagen 
    if(!$image  || $validate->fails()){
        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Error al subir la imagen'
        ];
    }else{
        $image_name = time().$image->getClientOriginalName();
        \Storage::disk('images')->put($image_name, \File::get($image));
        $data = [
            'code' =>200,
            'status' => 'success',
            'image' => $image_name
        ];

    }

    //devolver datos
    return response()->json($data, $data['code']);

   }
   public function getImage($filename){
    // comporbar si existe el fichero 
    $isset = \Storage::disk('images')->exists($filename);
    if($isset){
        //consegrui la imagen 
        $file = \Storage::disk('images')->get($filename);
        //devolver la imagen
         return new Response($file, 200);

    }else{
        $data = [
            'code' =>404,
            'status' => 'error',
            'message' => 'La imagen no existe'
        ];
    }
    return response()->json($data, $data['code']);

   }
public function getPostByCategory($id){
    $posts = Post::where('category_id', $id)->get();
    return response()->json([
        'status' => 'success',
        'posts' =>$posts
    ],200);
}
public function getPostsByUser($id){
    $posts = Post::where('user_id',$id)->get();

    return response()->json([
        'status' => 'success',
        'posts' =>$posts 

    ], 200);

}

    }
  