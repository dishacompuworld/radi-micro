<?php

// app/Http/Controllers/AlertMessageController.php

namespace App\Http\Controllers;

use App\Models\AlertMessage;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class AlertMessageController extends Controller
{
    /**
     * Constructor with middleware setup
     */
    public function __construct()
    {
        // $this->middleware('permission:show-alerts', ['only' => ['index', 'show']]);
        // $this->middleware('permission:add-alerts', ['only' => ['create', 'store']]);
        // $this->middleware('permission:edit-alerts', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:delete-alerts', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of alert messages
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $title = 'Alert Messages';

        if ($request->ajax()) {
            try {
                $alertMessages = AlertMessage::select(['id', 'type', 'message', 'msgcode', 'category', 'updated_at'])
                    ->orderBy('category')
                    ->orderBy('msgcode');
                
                return DataTables::of($alertMessages)
                    ->addIndexColumn()
                    ->addColumn('action', function ($data) {
                        $showbtn = ' <a href="'.route('alert.show', $data->id).'" class="btn btn-primary">Show</a>';
                        $editbtn = ' <a href="'.route('alert.edit', $data->id).'" class="btn btn-secondary">Edit</a>';
                        $deletebtn = ' <a href="javascript:void(0)" class="btn btn-danger" data-id="'.$data->id.'" id="delete-btn">Delete</a>';
                        
                        // Provide all buttons - permissions are handled at the route level by middleware
                        return $showbtn.' '.$editbtn.' '.$deletebtn;
                    })
                    ->addColumn('updated', function ($data) {
                        return date('D, d M Y g:i:s A', strtotime($data->updated_at));
                    })
                    ->rawColumns(['action', 'updated'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error generating DataTable: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to load data'], 500);
            }
        }
        
        return view('alert.index', compact('title'));
    }

    /**
     * Show the form for creating a new alert message
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $title = 'Create Alert Message';
        return view('alert.create', compact('title'));
    }

    /**
     * Store a newly created alert message
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|max:20',
                'message' => 'required|string',
                'catagory' => 'nullable|string|max:50',
                'msgcode' => 'required|string|max:50|unique:alert_messages,msgcode',
            ]);

            AlertMessage::create($validated);

            return redirect()->route('alert.index')
                ->with('success', 'Alert message created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create alert message: ' . $e->getMessage());
            return back()->with('error', 'Failed to create alert message')
                ->withInput();
        }
    }

    /**
     * Display the specified alert message
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $alertMessage = AlertMessage::findOrFail($id);
            $title = 'Alert Message Details';
            
            return view('alert.show', compact('alertMessage', 'title'));
        } catch (\Exception $e) {
            Log::error('Failed to show alert message: ' . $e->getMessage());
            return redirect()->route('alert.index')
                ->with('error', 'Alert message not found');
        }
    }

    /**
     * Show the form for editing an alert message
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $alertMessage = AlertMessage::findOrFail($id);
            $title = 'Edit Alert Message';
            
            return view('alert.edit', compact('alertMessage', 'title'));
        } catch (\Exception $e) {
            Log::error('Failed to edit alert message: ' . $e->getMessage());
            return redirect()->route('alert.index')
                ->with('error', 'Alert message not found');
        }
    }

    /**
     * Get an alert message by message code
     *
     * @param string $msgcode
     * @return AlertMessage|\Illuminate\Http\JsonResponse
     */
    public function get(string $msgcode)
    {
        try {
            $alertMessage = AlertMessage::where('msgcode', $msgcode)->first();
            
            if (!$alertMessage) {
                return (object)[
                    'type' => 'error',
                    'message' => "No message found for code: {$msgcode}"
                ];
            }
            
            return $alertMessage;
        } catch (\Exception $e) {
            Log::error("Failed to get alert message with code {$msgcode}: " . $e->getMessage());
            return (object)[
                'type' => 'error',
                'message' => 'An error occurred while retrieving the message'
            ];
        }
    }

    /**
     * Update the specified alert message
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $alertMessage = AlertMessage::findOrFail($id);
            
            $validated = $request->validate([
                'type' => 'required|string|max:20',
                'message' => 'required|string',
                'catagory' => 'nullable|string|max:50',
                'msgcode' => 'required|string|max:50|unique:alert_messages,msgcode,' . $id,
            ]);

            $alertMessage->update($validated);

            return redirect()->route('alert.index')
                ->with('success', 'Alert message updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update alert message: ' . $e->getMessage());
            return back()->with('error', 'Failed to update alert message')
                ->withInput();
        }
    }

    /**
     * Remove the specified alert message
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $alertMessage = AlertMessage::findOrFail($id);
            $alertMessage->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to delete alert message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete alert message']);
        }
    }
}
