<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
     {
         // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
         $this->middleware('permission:create-user',['only' => ['create','store']]);
         $this->middleware('permission:view-users',['only' => ['index']]);
         $this->middleware('permission:edit-user',['only' => ['edit','update','updateProfile','updatePassword']]);
         $this->middleware('permission:destroy-user',['only' => ['destroy']]);
     }

    public function index(Request $request)
    {
        $title = 'users';
        if ($request->ajax()) {
            $users = User::get();
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('created_at', function ($category) {
                    return date_format(date_create($category->created_at), "d M,Y");
                })
                ->addColumn('updated_at', function ($category) {
                    return date_format(date_create($category->updated_at), "d M,Y");
                })
                ->addColumn('avatar', function ($user) {
                    $src = asset('assets/img/avatar.png');
                    // if (!empty($user->avatar)) {
                    //     $src = asset('storage/users/'.$user->avatar);
                    // }
                    return '<img src="'.$src.'" class="avatar-img rounded-circle" width="50" />';
                })
                ->addColumn('role', function ($row) {
                    foreach ($row->getRoleNames() as $role) {
                        return '<span>'.$role.'</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("users.edit", $row->id).'" class="btn btn-sm btn-primary editbtn">Edit</a>';
                    $deletebtn = '<button type="button" class="btn btn-sm btn-danger deletebtn" data-id="'.$row->id.'" data-route="'.route('users.destroy', $row->id).'">Delete</button>';
                    if (!auth()->user()->hasPermissionTo('edit-user')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-user')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['avatar','role','action'])
                ->make(true);
        }
        return view('users.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create user';
        $roles = Role::get();
        $locations = Location::get();
        return view('users.create', compact('title','roles','locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|max:100',
            'email'=>'required|email',
            'role'=>'required',
            'password'=>'required|confirmed|max:200',
            'avatar'=>'nullable|file|image|mimes:jpg,jpeg,gif,png',
            'location'=>'nullable|array',
        ]);
        $locations = $request->location;
        $data = [];

        $imageName = null;
        if ($request->hasFile('avatar')) {
            $imageName = time().'.'.$request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $imageName,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);

        foreach ($locations ?? [] as $value) {
            $data = [
                'locationid' => $value,
                'userid' => $user->id,
            ];
            DB::table('userlocation')
            ->insert($data);
        }

        // $notifiation = notify('user created successfully');
        return redirect()->route('users.index');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $title = "edit user";
        $roles = Role::get();
        $locations = Location::get();

        $slocations = DB::table('userlocation')
        ->where('userid', $user->id)
        ->get();

        $srole=DB::table('model_has_roles')
        ->where('model_id', $user->id)
        ->get();

        // return $srole[0]->role_id;
        $srolename=DB::table('roles')
        ->where('id', $srole[0]->role_id)
        ->get();

        // return $srolename;

        return view('users.edit',compact(
            'title','roles','user','locations','slocations','srolename'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request,[
            'name'=>'required|max:100',
            'email'=>'required|email',
            'role'=>'required',
            'password'=>'nullable|confirmed|max:200',
            'avatar'=>'nullable|file|image|mimes:jpg,jpeg,gif,png',
            'location'=>'nullable|array',
        ]);

        $locations = $request->location;
        $data = [];
        $imageName = $user->avatar;
        $password = $user->password;
        if($request->hasFile('avatar')){
            $imageName = time().'.'.$request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        if(!empty($request->password) && ($user->password != $request->password)){
            $password = Hash::make($request->password);
        }
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $imageName,
            'password' => $password,
        ]);
        foreach($user->getRoleNames() as $userRole){
            $user->removeRole($userRole);
        }
        $user->assignRole($request->role);

        DB::table('userlocation')
                ->where('userid', $user->id)
                ->delete();

        foreach ($locations ?? [] as $value) {
            $data = [
                'locationid' => $value,
                'userid' => $user->id,
            ];
            DB::table('userlocation')
            ->insert($data);
        }

        // $notification = notify('user updated successfully');
        return redirect()->route('users.index');
    }

    public function profile(){
        $title = 'user profile';
        $roles = Role::get();
        return view('users.profile',compact(
            'title','roles'
        ));
    }

    public function updateProfile(Request $request,User $user){
        $this->validate($request,[
            'name' => 'required|min:5|max:200',
            'email' => 'required|email',
            'username' => 'nullable|min:3|max:200',
            'avatar' => 'nullable|file|image|mimes:jpg,jpeg,png,gif'
        ]);
        $imageName = $user->avatar;
        if($request->hasFile('avatar')){
            $imageName = time().'.'.$request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $imageName,
        ]);
        // $notification = notify('profile updated successfully');
        return redirect()->route('profile');
    }

    /**
     * Update current user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->validate($request, [
            'current_password'=>'required',
            'password'=>'required|max:200|confirmed',
        ]);
        $verify_password = password_verify($request->current_password, $user->password);
        if ($verify_password) {
            $user->update(['password'=>Hash::make($request->password)]);
            // $notification = notify('User password updated successfully!!!');
            $logout = auth()->logout();
            // return back()->with($notification, $logout);
            return back()->with($logout);
        } elseif(!$verify_password) {
            // $notification = notify("Incorrect Old Password!!!",'danger');
            // return back()->with($notification);
            return back();
        }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function destroy(User $user)
    {
        DB::table('userlocation')
            ->where('userid', $user->id)
            ->delete();

        return $user->delete();
    }
}
