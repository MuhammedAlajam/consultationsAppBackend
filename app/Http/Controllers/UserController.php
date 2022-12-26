<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User,Favorite};
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response     

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'username'=>'required|unique:users,username',
            'password'=>'required',
            'first_name'=>'required',
            'last_name'=>'required',
            'country'=>'required',
            'city'=>'required',
            'phone_number'=>'required|unique:users,phone_number'
        ]);
        $user=User::create([
            'username' => $request->input('username'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'profile_photo' => $request->input('profile_photo'),
            'phone_number' => $request->input('phone_number'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'wallet' => $request->input('wallet'),
            'password' =>bcrypt($request->input('password')),
            'role_type' =>'user'
        ]);
        return response()->json([
            'user'=>$user,
            'token'=>$user->createToken($user->username)->plainTextToken
        ],200);
    }


    public function login(Request $request)
    {
        $request->validate([
            'username'=>'required',
            'password'=>'required|min:8'
        ]);
        if(!Auth::attempt($request->only('username','password')))
        {
            return response()->json([
                'user'=>'',
                'token'=>''
            ],403);
        }
        $user=User::where('username',$request->username)->first();

        return response()->json([
            'user'=>$user,
            'token'=>$user->createToken($user->username)->plainTextToken
        ],200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return response()->json($user,200);
    }

    public function favorites()
    {
        $id = Auth::user()->id;
        $experts_ids = User::find($id)->favorites;
        $experts=[];
        foreach($experts_ids as $expert)
        {
            $expert_fav= User::find($expert->fav_id);
            $experts[]=[
                'id'=>$expert_fav->id,
                'username'=>$expert_fav->username,
                'first_name'=>$expert_fav->first_name,
                'last_name'=>$expert_fav->last_name,
                'rate'=>$expert_fav->expert->rate,
                'hourly_rate'=>$expert_fav->expert->hourly_rate

            ];
        }
        return response()->json($experts,200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function transfairMoney(Request $request)
    {
        $transfair = $request->input('transfair');
        $first =  User::find($request->input('id1'));
        $second =  User::find($request->input('id2'));

        $first->wallet -=  $transfair;
        $second->wallet +=  $transfair;
        $first->save();
        $second->save();

        return response()->json([],200);
    }

    public function addFavorite(Request $request)
    {
        $user= User::find($request->input('user_id'));
        $favorite= Favorite::create([
            'user_id'=>$request->input('user_id'),
            'fav_id'=>$request->input('fav_id')
        ]);
        return response()->json([],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return User::destroy($id);
    }
}