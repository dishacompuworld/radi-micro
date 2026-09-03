<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Server;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|max:55',
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if($validatedData->fails()){
            return response()->json($validatedData->errors(), 422);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);

    }

    public function login(Request $request){
        $validatedData = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]); 

        if($validatedData->fails()){
            return response()->json($validatedData->errors(), 422);
        }
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Invalid Credentials'], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'Bearer' => 'Bearer',
        ], 200);

    }
    public function user(Request $request){
        return Response()->json($request->user(), 200);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'],200);
    }

    // public function servers(){
    //     $servers = Server::all();
    //     return response()->json($servers, 200);
    // }
    public function server($id){
        $server = Server::find($id);
        if(!$server){
            return response()->json(['message' => 'Server not found'], 404);
        }
        return response()->json($server,200);
    }

    public function locations(){
        $locations = Location::all();
        return response()->json($locations,200);
    }

    public function location($id){
        $location = Location::find($id);
        if(!$location){
            return response()->json(['message' => 'Location not found'], 404);
        }
        return response()->json($location,200);
    }

    public function servers(){
        $servers = Server::all();

        activity()->causedBy(auth()->user())->useLog('Servers - api')->log('All Servers Fetched.');
        return response()->json($servers, 200);
    }

    public function getProfileapi(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(), // Returns a collection of role names
            'permissions' => $user->getAllPermissions()->pluck('name'), // Returns all permissions (direct + via roles)
        ]);
    }
}
