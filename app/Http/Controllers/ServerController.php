<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Replace;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{
    private $validationRules = [
        'name' => 'required|string|max:255',
        'sname' => 'required|string|max:50',
        'ip' => 'required|ipv4',
        'ip2' => 'nullable|ipv4',
        'susername' => 'required|string|max:50',
        'pass' => 'required|string|min:6',
        'enable' => 'boolean'
    ];

    private $validationMessages = [
        'name.required' => 'Server name is required',
        'name.string' => 'Server name must be a string',
        'name.max' => 'Server name cannot exceed 255 characters',
        'sname.required' => 'Short name is required',
        'sname.string' => 'Short name must be a string',
        'sname.max' => 'Short name cannot exceed 50 characters',
        'ip.required' => 'IP address is required',
        'ip.ipv4' => 'IP address must be in IPv4 format',
        'ip2.ipv4' => 'Secondary IP address must be in IPv4 format',
        'susername.required' => 'Server login username is required',
        'susername.string' => 'Username must be a string',
        'susername.max' => 'Username cannot exceed 50 characters',
        'pass.required' => 'Server password is required',
        'pass.string' => 'Password must be a string',
        'pass.min' => 'Password must be at least 6 characters',
        'enable.boolean' => 'Enable flag must be a boolean'
    ];

    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:add-server',['only' => ['create','store']]);
        // $this->middleware('permission:view-server',['only' => ['index','show']]);
        // $this->middleware('permission:update-server',['only' => ['edit','update']]);
        // $this->middleware('permission:delete-server',['only' => ['destroy']]);
    }


    public function index()
    {
        try {
            $title = "View Microtiks";
            $servers = Server::orderBy('name')->get();
            return view("server.index", compact('title', 'servers'));
        } catch (\Exception $e) {
            Log::error("Error in server index: " . $e->getMessage());
            return back()->with('error', 'Failed to fetch servers');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $title = "Add Microtik";
            return view('server.add', compact('title'));
        } catch (\Exception $e) {
            Log::error("Error in server create: " . $e->getMessage());
            return back()->with('error', 'Failed to load create form');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request;

        $request->validate([
            'name' => 'required',
            'sname' => 'required',
            'ip'=> 'required|ipv4',
            'susername' => 'required',
            'pass' => 'required'

        ],
        [
            'name.required' => 'Servername is Required',
            'sname.required' => 'Short name is Required',
            'ip.ipv4' => 'IP is Required in IPV4 format',
            'ip.required' => 'IP address is Required',
            'susername.required' => 'Server Login username is Required',
            'pass.required' => 'Server password is Required'

        ]);

        try {
            $server = new Server;

            $server->name = $request->name;
            $server->shortname = $request->sname;
            $server->mip = $request->ip;
            $server->ip2 = $request->ip2;
            $server->username = $request->susername;
            $server->password = $request->pass;

            if($request->enable){
                $server->enable=1;
            }else{
                $server->enable=0;
            }

            // $server->enable = $request->enable;

            $server->save();

            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.add.success');
            $msg = str_replace(':server', $server->name, $alertMessage->message);
            $type = $alertMessage->type;
            
        } catch (\Exception $e) {
            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.add.error');
            $msg = $alertMessage->message;
            $type = $alertMessage->type;
        }
        return redirect()->route('server.index')->with($type,$msg);
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        $title='Show Server';
        $servers = Server::find($server->id);
        try {
            $title = "Server Details";
            return view('admin.server.show', compact('title', 'server','servers'));
        } catch (\Exception $e) {
            Log::error("Error in server show: " . $e->getMessage());
            return back()->with('error', 'Failed to load server details');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        $title='Edit Server';
        $servers = Server::find($server->id);
        try {
            $title = "Edit Server";
            return view('admin.server.update', compact('title', 'server','servers'));
        } catch (\Exception $e) {
            Log::error("Error in server edit: " . $e->getMessage());
            return back()->with('error', 'Failed to load edit form');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server)
    {
        try {
            $validated = $request->validate($this->validationRules, $this->validationMessages);

            $server->update([
                'name' => $validated['name'],
                'shortname' => $validated['sname'],
                'mip' => $validated['ip'],
                'ip2' => $validated['ip2'] ?? null,
                'username' => $validated['susername'],
                'password' => $validated['pass'],
                'enable' => $request->boolean('enable', false)
            ]);

            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.update.success');
            $msg = str_replace(':server', $server->name, $alertMessage->message);
            
            return redirect()->route('server.index')
                ->with($alertMessage->type, $msg);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error in server update: " . $e->getMessage());
            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.update.error');
            return back()->with($alertMessage->type, $alertMessage->message)->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        try {
            $serverName = $server->name;
            $server->delete();

            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.delete.success');
            $msg = str_replace(':server', $serverName, $alertMessage->message);
            
            return redirect()->route('server.index')
                ->with($alertMessage->type, $msg);
        } catch (\Exception $e) {
            Log::error("Error in server destroy: " . $e->getMessage());
            $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('server.delete.error');
            return back()->with($alertMessage->type, $alertMessage->message);
        }
    }
}
