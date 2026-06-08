<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     public function __construct()
    {
        // // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:view-permission',['only' => ['index']]);
        // $this->middleware('permission:edit-permission',['only' => ['update']]);
        // $this->middleware('permission:create-permission',['only' => ['store']]);
        // $this->middleware('permission:destroy-permission',['only' => ['destroy']]);

    }

    public function index(Request $request)
    {
        $title = 'permissions';
        if ($request->ajax()){
            $permissions = Permission::get();
            // return dd($permissions);
            return DataTables::of($permissions)
                    ->addIndexColumn()
                    ->addColumn('created_at',function($row){
                        return date_format(date_create($row->created_at),'D M Y');
                    })
                    ->addColumn('action',function ($row){
                        $editbtn = '<button type="button" data-id="'.$row->id.'" data-name="'.$row->name.'" data-route="'.route('permissions.update',$row->id).'" class="btn btn-sm btn-primary editbtn" title="Edit"><i class="bx bx-edit"></i></button>';
                        $deletebtn = '<button type="button" data-id="'.$row->id.'" data-route="'.route('permissions.destroy',$row->id).'" class="btn btn-sm btn-danger deletebtn" title="Delete"><i class="bx bx-trash"></i></button>';
                        if(!auth()->user()->hasPermissionTo('edit-permission')){
                            $editbtn = '';
                        }
                        if(!auth()->user()->hasPermissionTo('destroy-permission')){
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('roles.permissions',compact(
            'title',
        ));
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
            'permission' => 'required|min:3|max:255'
        ]);
        foreach (explode(',',$request->permission) as $permission){
            $permission = Permission::create(['name' => $permission]);
            $permission->assignRole('super-admin');
        }
        // $notification = notify("permission created");
        return back();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $this->validate($request,[
            'permission' => 'required|min:3|max:255'
        ]);
        $permission->update([
            'name' => $request->permission,
        ]);
        // $notification = notify('permission updated');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
