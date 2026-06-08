<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    
    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        $this->middleware('permission:view-role',['only' => ['index','store']]);
        $this->middleware('permission:create-role',['only' => ['create']]);
        $this->middleware('permission:destroy-role',['only' => ['destroy']]);
        $this->middleware('permission:edit-role',['only' => ['edit','update']]);

    }
    
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'roles';
        if($request->ajax()){
            $roles = Role::get();
            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('permissions',function ($role){
                    return $role->getAllPermissions()
                        ->map(function ($permission) {
                            return '<span class="badge bg-label-primary me-1 mb-1">'.$permission->name.'</span>';
                        })
                        ->implode('');
                })
                ->addColumn('action',function ($row){
                    $editbtn = '<a href="'.route('roles.edit',$row->id).'" class="btn btn-sm btn-primary" title="Edit"><i class="bx bx-edit"></i></a>';
                    $deletebtn = '<button type="button" data-id="'.$row->id.'" data-route="'.route('roles.destroy',$row->id).'" class="btn btn-sm btn-danger deletebtn" title="Delete"><i class="bx bx-trash"></i></button>';
                    if(!auth()->user()->hasPermissionTo('edit-role')){
                        $editbtn = '';
                    }
                    if(!auth()->user()->hasPermissionTo('destroy-role')){
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['permissions','action'])
                ->make(true);
        }
        return view('roles.index',compact(
           'title' 
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create role';
        $permissions = Permission::get();
        return view('roles.create',compact('title','permissions'));
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
            'role' => 'required|min:3|max:255',
            'permission' => 'required',
        ]);
        $role = Role::create(['name' => $request->role]);
        $role->syncPermissions($request->permission);
        // $notification = notify('role created successfully');
        return redirect()->route('roles.index');
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $title = 'edit role';
        $permissions = Permission::get();
        return view('roles.edit',compact(
            'title','role','permissions'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $this->validate($request,[
            'role' => 'required|min:3|max:255',
            'permission' => 'required'
        ]);
        $role->update([
            'name' => $request->role,
        ]);
        $role->syncPermissions($request->permission);
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
