<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Acamposm\SnmpPoller\SnmpPoller;
// use Acamposm\SnmpPoller\Pollers\IfTablePoller;
use SNMP;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Log;
Use Exception;
use Carbon\Carbon;

class FetchSNMP extends Controller
{
    /**
     * SNMP connection parameters
     */
    private $snmpHost;
    private $snmpCommunity;
    private $snmpVersion;
    
    /**
     * OIDs for various SNMP operations
     */
    private $oidNames;
    private $oidPowers;
    private $oidPowersTx;
    private $oidRegist;
    private $minOntPower;
    
    /**
     * Constructor with middleware setup and configuration loading
     */
    public function __construct()
    {
        // Load configuration values from environment
        $this->snmpHost = env('OLT_IP', '127.0.0.1');
        $this->snmpCommunity = env('SNMP_COMMUNITY', 'public');
        $this->snmpVersion = SNMP::VERSION_1;
        
        $this->oidNames = env('SNMP_OID_NAMES');
        $this->oidPowers = env('SNMP_OID_POWERS');
        $this->oidPowersTx = env('SNMP_OID_POWERS_TR');
        $this->oidRegist = env('SNMP_OID_REGIST');
        $this->minOntPower = env('MIN_ONT_POWER');
        
        // Set up middleware permissions
        // $this->middleware('permission:fetch-op-power',['only' => ['insertintodb']]);
        // $this->middleware('permission:show-op-power',['only' => ['showop']]);
        // $this->middleware('permission:assign-optical-power',['only' => ['assignpower']]);
        // $this->middleware('permission:add-ont',['only' => ['addont']]);
        // $this->middleware('permission:register-ont',['only' => ['registont','deregistont']]);
        // $this->middleware('permission:rename-ont',['only' => ['renameont']]);
        // $this->middleware('permission:delete-ont',['only' => ['deleteont']]);
    }
    
    /**
     * Create an SNMP session with proper configuration
     * 
     * @param int $version SNMP version to use
     * @return SNMP
     */
    private function createSnmpSession($version = null)
    {
        // Configure SNMP settings
        snmp_set_quick_print(1);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        
        // Create session with provided or default version
        return new SNMP($version ?? $this->snmpVersion, $this->snmpHost, $this->snmpCommunity);
    }
    
    /**
     * Execute SNMP get operation with error handling
     * 
     * @param string $oid The OID to query
     * @param bool $trimQuotes Whether to trim quotes from result
     * @param int $version SNMP version to use
     * @return string|null
     */
    private function snmpGet($oid, $trimQuotes = true, $version = null)
    {
        try {
            $session = $this->createSnmpSession($version);
            $result = $session->get($oid);
            $session->close();
            
            return $trimQuotes ? trim($result, '"') : $result;
        } catch (Exception $e) {
            Log::error("SNMP GET error for OID {$oid}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Execute SNMP set operation with error handling
     * 
     * @param string $oid The OID to set
     * @param string $type The type of the value
     * @param mixed $value The value to set
     * @param int $version SNMP version to use
     * @return bool
     */
    private function snmpSet($oid, $type, $value, $version = null)
    {
        try {
            $session = $this->createSnmpSession($version ?? SNMP::VERSION_2C);
            $session->set($oid, $type, $value);
            $session->close();
            
            return true;
        } catch (Exception $e) {
            Log::error("SNMP SET error for OID {$oid}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute SNMP walk operation with error handling
     * 
     * @param string $oid The OID to walk
     * @param int $version SNMP version to use
     * @return array|null
     */
    private function snmpWalk($oid, $version = null)
    {
        try {
            $session = $this->createSnmpSession($version);
            $result = $session->walk($oid);
            $session->close();
            
            return $result;
        } catch (Exception $e) {
            Log::error("SNMP WALK error for OID {$oid}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log user activity
     * 
     * @param string $message The log message
     * @param string $logName The log name
     * @return void
     */
    private function logActivity($message, $logName = 'Optical Power')
    {
        activity()
            ->causedBy(auth()->user())
            ->useLog($logName)
            ->log($message);
    }

    private function logActivityapi($message, $logName = 'Optical Power-Api')
    {
        activity()
            ->causedBy(auth()->user())
            ->useLog($logName)
            ->log($message);
    }
    
    /**
     * Get ONT power by OID
     * 
     * @param string $oid The ONT OID
     * @return string
     */
    public function getontpower($oid)
    {
        $newoid = $this->oidPowers . "." . $oid;
        $power = $this->snmpGet($newoid);

        // Get the ONT details from database
        $ontRecord = DB::table('opticalpowers')
            ->where('oid', $oid)
            ->first();
        
        if ($power !== null) {
            // Update the database with new power value
            $currentDateTime = now();
            
            DB::table('opticalpowers')
                ->where('oid', $oid)
                ->update([
                    'powers' => $power,
                    'updated_at' => $currentDateTime
                ]);

            $message = "Optical Power updated for {$ontRecord->name}, old optical power: {$ontRecord->powers}dBm, new optical power: {$power}dBm";
            $this->logActivity($message);
            return $power;
        }
        
        return "SNMP Not Available";
    }
    


    public function getontpowerapi($oid)
    {
         $newoid = $this->oidPowers . "." . $oid;
        $power = $this->snmpGet($newoid);

        // Get the ONT details from database
        $ontRecord = DB::table('opticalpowers')
            ->where('oid', $oid)
            ->first();
        
        if ($power !== null) {
            // Update the database with new power value
            $currentDateTime = now();
            
            DB::table('opticalpowers')
                ->where('oid', $oid)
                ->update([
                    'powers' => $power,
                    'updated_at' => $currentDateTime
                ]);

            $message = "Optical Power updated for {$ontRecord->name}, old optical power: {$ontRecord->powers}dBm, new optical power: {$power}dBm";
            $this->logActivityapi($message);
            return $power;
        }
        
        return "SNMP Not Available";
    }
    
    /**
     * Get ONT TX power by OID
     * 
     * @param string $oid The ONT OID
     * @return string
     */
    public function getonttxpower($oid)
    {
        $newoid = $this->oidPowersTx . "." . $oid;
        $power = $this->snmpGet($newoid);
        
        return $power ?? "SNMP Not Available";
    }
    
    /**
     * Get ONT name by OID
     * 
     * @param string $oid The ONT OID
     * @return string
     */
    public function getontname($oid)
    {
        $newoid = $this->oidNames . "." . $oid;
        $name = $this->snmpGet($newoid);
        
        return $name ?? "SNMP Not Available";
    }
    
    /**
     * Update ONT optical power
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateop(Request $request)
    {
        $oid = $request->input('variable');
        $user = $request->input('user');
        $ont = $request->input('ont');
        
        // Get the ONT details from database
        $ontRecord = DB::table('opticalpowers')
            ->where('oid', $oid)
            ->first();
            
        if (!$ontRecord) {
            return response()->json([
                'success' => false,
                'user' => $user,
                'ont' => $ont,
                'message' => 'ONT record not found'
            ]);
        }
        
        // Get the power from SNMP
        $power = $this->getontpower($oid);
        
        if ($power !== "SNMP Not Available") {
            
            return response()->json([
                'success' => true, 
                'user' => $user,
                'ont' => $ont,
                'op' => $power
            ]);
        }
        
        $message = "Optical Power update failed for {$ontRecord->name}";
        $this->logActivity($message);
        
        return response()->json([
            'success' => false,
            'user' => $user,
            'ont' => $ont,
            'message' => 'SNMP connection failed'
        ]);
    }


    public function updateopapi(Request $request)
    {
        $oid = $request->input('oid');
        // $ont = $request->input('ont');
        
        // Get the ONT details from database
        $ontRecord = DB::table('opticalpowers')
            ->where('oid', $oid)
            ->first();
            
        if (!$ontRecord) {
            return response()->json([
                'success' => false,
                'message' => 'ONT record not found'
            ]);
        }
        
        // Get the power from SNMP
        $power = $this->getontpower($oid);
        
        if ($power !== "SNMP Not Available") {
            $message = "Optical Power updated for {$ontRecord->name}, old optical power: {$ontRecord->powers}dBm, new optical power: {$power}dBm";
            
            return response()->json([
                'success' => true,
                'message' => $message           
            ]);
        }
        
        $message = "Optical Power update failed for {$ontRecord->name}";
        $this->logActivityapi($message);
        
        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }
    
    /**
     * Delete ONT from database
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteont(Request $request)
    {
        $oid = $request->input('variable');
        
        try {
            DB::table('opticalpowers')
                ->where('oid', $oid)
                ->delete();
                
            $message = "ONT {$oid} deleted";
            $this->logActivity($message);
            
            return response()->json([
                'success' => true, 
                'oid' => $oid
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting ONT {$oid}: " . $e->getMessage());
            
            $message = "ONT {$oid} deletion failed";
            $this->logActivity($message);
            
            return response()->json([
                'success' => false, 
                'oid' => $oid,
                'message' => 'Database operation failed'
            ]);
        }
    }


    public function deleteontapi(Request $request)
    {
        $oid = $request->input('oid');
        
        try {
            DB::table('opticalpowers')
                ->where('oid', $oid)
                ->delete();
                
            $message = "ONT {$oid} deleted successfully";
            $this->logActivity($message);
            
            return response()->json([
                'success' => true, 
                'message' => $message
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting ONT {$oid}: " . $e->getMessage());
            
            $message = "ONT {$oid} deletion failed";
            $this->logActivityapi($message);
            
            return response()->json([
                'success' => false,
                'message' => $message
            ]);
        }
    }
    
    /**
     * Deregister ONT via SNMP
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deregistont(Request $request)
    {
        $oid = $request->input('oid');
        $newoid = $this->oidRegist . "." . $oid;
        
        $success = $this->snmpSet($newoid, 'i', 0, SNMP::VERSION_2C);
        
        if ($success) {
            $message = "ONT {$oid} successfully de-registered";
            $this->logActivity($message);
            
            return response()->json([
                'success' => true
            ]);
        }
        
        $message = "ONT {$oid} de-registration failed";
        $this->logActivity($message);
        
        return response()->json([
            'success' => false,
            'message' => 'SNMP operation failed'
        ]);
    }

    public function getontnames(){
        snmp_set_quick_print(1);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        $session = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        $fulltree = $session->walk("1.3.6.1.4.1.11863.6.100.1.7.2.1.3.1");
        $allonts = json_encode($fulltree);
        $session->close();

        return $allonts;
        // foreach ($allonts as $ont){
        //     echo $ont.'<br>';
        // }
    }

    public function getontpowers(){

        snmp_set_quick_print(1);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        $session = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        $fulltree = $session->walk("1.3.6.1.4.1.11863.6.100.1.7.2.1.22.1");
        // return json_encode($fulltree);
        $session->close();

        $nearray = array();
        foreach ($fulltree as $key => $value) {
            // $value_from_array1 = isset($array1[$key]) ? $array1[$key] : null;
            // echo substr($key,37) . " " . $value . "<br>";

            // $nearray[] = array(substr($key,37) => $vnearrayalue);
            $nearray[] = [substr($key,37) => $value];

        }
        foreach ($nearray as $key => $value) {
            echo $key . " " . $value . "<br>";
        }

        // return $nearray;
    }

    public function showtest1(){
        $session = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        $fulltree = $session->get("iso.3.6.1.4.1.11863.6.100.1.7.2.1.3.2.1");
        return json_encode($fulltree);
        $session->close();
    }

    public function showtestnames(){
        snmp_set_quick_print(1);

        $session = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        $fulltree = $session->walk("iso.3.6.1.4.1.11863.6.100.1.7.2.1.3.1");
        // return $fulltree;
        $session->close();

        $session = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        $fulltree1 = $session->walk("1.3.6.1.4.1.11863.6.100.1.7.2.1.22.1");
        // return json_encode($fulltree);
        $session->close();

        // $array = ["One" => 1, "Two" => 2, "Three" => 3];
        // $array1 = ["Four" => 4, "Five" => 5, "Six" => 6];

        // foreach ($fulltree as $key => $value) {
        //     // $value_from_array1 = isset($array1[$key]) ? $array1[$key] : null;
        //     echo $key . " " . $value . " " . $array1[$key] . "<br>";

        // }
    }

    public function showtestpowers(){

        // snmp_set_quick_print(1);
        // $sessionname = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        // $fulltreenames = $sessionname->walk("1.3.6.1.4.1.11863.6.100.1.7.2.1.22.1");
        // // return $fulltree;
        // $sessionname->close();

        // snmp_set_quick_print(1);
        // $sessionpower = new SNMP(SNMP::VERSION_1, "103.248.30.206", "public");
        // $fulltreepowers = $sessionpower->walk("1.3.6.1.4.1.11863.6.100.1.7.2.1.3.1");
        // // return json_encode($fulltree);
        // $sessionpower->close();

        // // foreach ($fulltree as $ont=>$val){
        // //          echo $ont . ' '. $val .'<br>';
        // //      }

        // $newarray = array_combine($fulltreenames, $fulltreepowers);
        //     //  foreach ($newarray as $nwar =>$nw) {

        //     //     $nname = substr($name,9,-1);

        //     //     $nop=substr($op,9);

        //     //     DB::table('opticalpowers')
        //     //     ->insert([
        //     //         'name' => $nname,
        //     //         'powers' => $nop,
        //     //     ]);
        //     // }

        //     return $newarray;
    }

    public function insertintodb(Request $request){
        snmp_set_quick_print(1);
        $namesession = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
        $names = $namesession->walk(env('SNMP_OID_NAMES',null));
        // $allonts = json_encode($fulltree);
        $namesession->close();

        snmp_set_quick_print(1);
        $opsession = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
        $opticalpower = $opsession->walk(env('SNMP_OID_POWERS',null));
        // return json_encode($fulltree);
        $opsession->close();


        DB::table('opticalpowers')->delete();
        DB::statement('ALTER TABLE opticalpowers AUTO_INCREMENT = 1');

        foreach($names as $keys=>$value){

            $oid = substr($keys, 36);

            DB::table('opticalpowers')
            ->insert([
                'oid' => $oid,
                'name' => trim($value, '"'),
            ]);
        }
        foreach($opticalpower as $keys=>$value){

            $oid = substr($keys, 37);
            $current_date_time = date('Y-m-d H:i:s');

            DB::table('opticalpowers')
            ->where('oid', $oid)
            ->update([
                'powers' => $value,
                'updated_at'=>$current_date_time
            ]);
        }

        $msg = 'Fetched successfully from OLT and inserted in Database';
        activity()->causedBy(auth()->user())->useLog('Optical Power')->log($msg);

        $title = "Optical Powers";

        if($request->ajax()){

            $ops = DB::table('opticalpowers')->get();

            return DataTables::of($ops)
                    ->addIndexColumn()
                    ->make(true);
            return view('admin.snmp.op', compact('title'));
        }

        return view('admin.snmp.op', compact('title'));


        // $this->showop();

    }

    public function showop(Request $request){
        $title = "Optical Powers";

        $lastchk = DB::table('opticalpowers')->orderBy('updated_at', 'ASC')->first();

        // return $lastchk;
        // Log::info('Last Checked OP value: ' . json_encode($lastchk));
        if($lastchk){
            // $username = User::find($lastchk->causer_id)->name;

            $updval = $lastchk->updated_at;
            // return $updval;
        }else{
            // $username = "";
            $updval = "";
        }

        if($request->ajax()){

            $ops = DB::table('opticalpowers')
            ->select('opticalpowers.srno as srno','opticalpowers.name as name','opticalpowers.oid as oid', 'opticalpowers.powers as power','subscribers.name as username', 'opticalpowers.updated_at as update')
            ->leftJoin('subscribers', 'opticalpowers.oid', '=', 'subscribers.oid')
            ->get();

            return DataTables::of($ops)
                    ->addIndexColumn()
                    ->addColumn('opower',function ($data){
                        if($data->power <= $this->minOntPower){
                            $status = '<p class="text-danger"><b>'. $data->power . '</b></p>';
                        }else{
                            $status = '<p class="text-success">'. $data->power . '</p>';
                        }
                        return $status;
                    })
                    ->addColumn('names',function ($data){
                        if($data->power <= $this->minOntPower){
                            $status = '<a href="'.route('edit.ont', ['oid' => $data->oid]).'"><p class="text-danger"><b>'. $data->name . '</b></p></a>';
                        }else{
                            $status = '<a href="'.route('edit.ont', ['oid' => $data->oid]).'"><p class="text-success">'. $data->name . '</p></a>';
                        }
                        return $status;
                    })
                    ->addColumn('user',function ($data){
                        // $status = '<p class="text-danger"><b>'. $data->name . '</b></p>';
                        $status = '<a href="'.route('subscriber.microtik', ['name' => $data->username]).'">'. $data->username .'</a>';

                    return $status;
                    })
                    ->addColumn('action',function ($data){
                            $refreshbtn = ' <a href="javascript:void(0)" class="btn btn-primary" data-route="'.route('update.ont.power').'" data-variable="'. $data->oid .'" data-ont="'. $data->name .'" data-user= "'. $data->username .'" id="refreshbtn" ><i class="bi bi-arrow-repeat"></i></a>';
                            $deletebtn = ' <a href="javascript:void(0)" class="btn btn-danger" data-routee="'.route('delete.ont').'" data-oid="'. $data->oid .'" data-ont="'. $data->name .'" data-user= "'. $data->username .'" id="deletebtn"><i class="fas fa-trash"></i></a>';
                            $rebootbtn = ' <a href="javascript:void(0)" class="btn btn-danger" data-routee="'.route('reboot.ont').'" data-oid="'. $data->oid .'" data-ont="'. $data->name .'" data-user= "'. $data->username .'" id="rebootbtn"><i class="bi bi-power"></i></a>';
                            
                            // The middleware handling permission 'delete-ont' will prevent access at the route level
                            // No need to check permissions again here
                            $btn = $refreshbtn.' '.$deletebtn. ' '. $rebootbtn;
                            return $btn;
                    })
                    ->addColumn('updated',function ($data){
                        // $btn = $data->update->format('Y-m-d H:i:s');
                        $btn = date('D, d M Y g:i:s A', strtotime($data->update));
                        return $btn;
                    })
                    ->rawColumns(['opower','names','user','action','updated'])
                    ->make(true);

            return view('admin.snmp.op', compact('title','updval'));
        }

        $msg = 'Optical Powers fetched from Database';
        activity()->causedBy(auth()->user())->useLog('Optical Power')->log($msg);
        return view('snmp.op', compact('title','updval'));
    }

    public function showopapi(Request $request){
        // $title = "Optical Powers";

        $lastchk = DB::table('opticalpowers')->orderBy('updated_at', 'ASC')->first();

        // return $lastchk;
        // Log::info('Last Checked OP value: ' . json_encode($lastchk));
        if($lastchk){
            // $username = User::find($lastchk->causer_id)->name;

            $updval = $lastchk->updated_at;
            // return $updval;
        }else{
            // $username = "";
            $updval = "";
        }

        // if($request->ajax()){

            $ops = DB::table('opticalpowers')
            ->select('opticalpowers.srno as srno','opticalpowers.name as name','opticalpowers.oid as oid', 'opticalpowers.powers as power','subscribers.name as username', 'opticalpowers.updated_at as update')
            ->leftJoin('subscribers', 'opticalpowers.oid', '=', 'subscribers.oid')
            ->get();

           
            $msg = 'Optical Powers fetched from Database';
            activity()->causedBy(auth()->user())->useLog('Optical Power - API')->log($msg);

            // return view('admin.snmp.op', compact('title','updval'));
            return response()->json($ops, 200);
        // }

    
        // return view('admin.snmp.op', compact('title','updval'));
    }

    public function assignpower(Request $request){
        $title= "Assing ONT";
        $username = $request->name;
        $optdata = DB::table('opticalpowers')->get();

        // return $request->sub;

        if(($request->oid) && ($username!="") && ($request->sub=="yes")){

            // return $request->oid . " " . $username;
            DB::table('subscribers')
            ->where('name', $username)
            ->update([
                'oid'=>$request->oid,
            ]);

            return redirect()->route('subscriber.microtik', ['name'=>$username]);
        }

        return view('admin.snmp.assignont', compact('title','username','optdata'));
    }

    public function getontuptime($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_UPTIME',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $ontuptime = $session->get($newoid);
            $session->close();

            $newontuptime = trim($ontuptime, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $newontuptime = "Not Available";
            }
        }
        return $newontuptime;
    }

    public function getontserial($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_BRAND',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $ontserial = $session->get($newoid);
            $session->close();

            $newontserial = trim($ontserial, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $newontserial = "Snmp Not Available";
            }
        }
        return $newontserial;
    }

    public function getonttemp($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_TEMP',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $onttemp = $session->get($newoid);
            $session->close();

            $newonttemp = trim($onttemp, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $newonttemp = "Not Available";
            }
        }
        return $newonttemp;
    }

    public function getonteth($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_ETH',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $onteth = $session->get($newoid);
            $session->close();

            $newonteth = trim($onteth, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $newonteth = "Snmp Not Available";
            }
        }
        return $newonteth;
    }

    public function getontmodel($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_MODEL',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $ontmodel = $session->get($newoid);
            $session->close();

            $newontmodel = trim($ontmodel, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $newontmodel = "Snmp Not Available";
            }
        }
        return $newontmodel;
    }

    public function getontdist($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_DIST',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $ontdist = $session->get($newoid);
            $session->close();

            //$newontmodel = trim($ontmodel, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $ontdist = "Snmp Not Available";
            }
        }
        return $ontdist;
    }

    public function getontstatus($oid){
        snmp_set_quick_print(1);
        $newoid = env('SNMP_OID_STATUS',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            $ontstatus = $session->get($newoid);
            $session->close();

            //$newontmodel = trim($ontmodel, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $ontstatus = "Snmp Not Available";
            }
        }
        return $ontstatus;
    }

    public function renameont(Request $request){
        $title = "Edit ONT Name";
        $optdata = DB::table('opticalpowers')->get();
        $oid=$request->oid;
        $type = "";
        $msg = "";

        if($oid){
            $opticalpower = $this->getontpower($oid);
            if($opticalpower=="Snmp Not Available"){
                $onttxpower = "Snmp Not Available";
                $ontname = "Snmp Not Available";
                $ontuptime = "Snmp Not Available";
                $ontserial = "Snmp Not Available";
                $onttemp = "Snmp Not Available";
                $onteth = "Snmp Not Available";
                $ontmodel = "Snmp Not Available";
                $ontdist = "Snmp Not Available";
                $ontstatus = "Snmp Not Available";
            }else{
                $ontname = $this->getontname($oid);
                $onttxpower = $this->getonttxpower($oid);
                $ontuptime = $this->getontuptime($oid);
                $ontserial = $this->getontserial($oid);
                $onttemp = $this->getonttemp($oid);
                $onteth = $this->getonteth($oid);
                $ontmodel = $this->getontmodel($oid);
                $ontdist = $this->getontdist($oid);
                $ontstatus = trim($this->getontstatus($oid),'"');
            }
            
        }else{
            $opticalpower =  "";
            $onttxpower = "";
            $ontuptime = "";
            $ontserial = "";
            $onttemp = "";
            $onteth = "";
            $ontname = "";
            $ontmodel = "";
            $ontdist = "";
            $ontstatus = "";
        }

        if($request->ontnewname){
            try
            {
                $newoid = (env('SNMP_OID_NAMES',null)). "." . $oid;
                $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
                $session->set($newoid,'s', $request->ontnewname);

                $session->close();

                DB::table('opticalpowers')
                ->where('oid', $oid)
                ->update([
                    'name' => $request->ontnewname
                ]);


                $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('ont.update.success');
                $msg = str_replace(':oid', $oid, $alertMessage->message);
                $type = $alertMessage->type;
            }
            catch(Exception $e)
            {
                if ($e->getMessage()){
                    // return 'false';
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('ont.update.error');
                    $msg = str_replace(':oid', $oid, $alertMessage->message);
                    $type = $alertMessage->type;
                }
            }
            // $msg = 'Ont '. $ontname . " Change to " . $request->ontnewname;
            activity()->causedBy(auth()->user())->useLog('ONT Edit')->log($msg);

            $ontname = $request->ontnewname;
        }
        if($type == 'success'){
            session()->flash('success', $msg);
            // return view('admin.snmp.addont', compact('title'));
        }else{
            session()->flash('error', $msg);
            // return view('admin.snmp.addont', compact('title'));
        }
        return view('snmp.editont', compact('title','optdata','oid','opticalpower','ontuptime','ontserial','onttemp','onteth','ontname','ontmodel','ontdist','onttxpower','ontstatus'));
    }

    public function getontdetailsapi(Request $request){
        $optdata = DB::table('opticalpowers')->get();
        $oid=$request->oid;
        $type = "";
        $msg = "";

        if($oid){
            $opticalpower = $this->getontpowerapi($oid);
            if($opticalpower=="Snmp Not Available"){
                $onttxpower = "Snmp Not Available";
                $ontname = "Snmp Not Available";
                $ontuptime = "Snmp Not Available";
                $ontserial = "Snmp Not Available";
                $onttemp = "Snmp Not Available";
                $onteth = "Snmp Not Available";
                $ontmodel = "Snmp Not Available";
                $ontdist = "Snmp Not Available";
                $ontstatus = "Snmp Not Available";
            }else{
                $ontname = $this->getontname($oid);
                $onttxpower = $this->getonttxpower($oid);
                $ontuptime = $this->getontuptime($oid);
                $ontserial = $this->getontserial($oid);
                $onttemp = $this->getonttemp($oid);
                $onteth = $this->getonteth($oid);
                $ontmodel = $this->getontmodel($oid);
                $ontdist = $this->getontdist($oid);
                $ontstatus = trim($this->getontstatus($oid),'"');
            }
            
        }else{
            $opticalpower =  "";
            $onttxpower = "";
            $ontuptime = "";
            $ontserial = "";
            $onttemp = "";
            $onteth = "";
            $ontname = "";
            $ontmodel = "";
            $ontdist = "";
            $ontstatus = "";
        }

        if($opticalpower){
            return response()->json([
                'success' => true,
                'oid' => $oid,
                'opticalpower' => $opticalpower,
                'ontuptime' => $ontuptime,
                'ontserial' => $ontserial,
                'onttemp' => $onttemp,
                'onteth' => $onteth,
                'ontname' => $ontname,
                'ontmodel' => $ontmodel,
                'ontdist' => $ontdist,
                'onttxpower' => $onttxpower,
                'ontstatus' => $ontstatus
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'ONT details not found'
            ]);
        }
    }

    public function addont(Request $request){
        $title = "Add ONT to Database";
        $msg = "";
        $type = "";
        // $optdata = DB::table('opticalpowers')->get();
        try{
            if($request->oid){
                $insert = DB::table('opticalpowers')
                ->insert([
                    'oid' => $request->oid,
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);

                if($insert){
                    // $msg = "New OID(" . $request->oid .") Added";
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('add.ont.success');
                    $msg = str_replace(':oid', $request->oid, $alertMessage->message);
                    $type = $alertMessage->type;
                }else{
                    // $msg = "OID not added";
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('add.ont.error');
                    $msg = $alertMessage->message;
                    $type = $alertMessage->type;
                }

                activity()->causedBy(auth()->user())->useLog('Add OID')->log($msg);
            }
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $msg = $e->getMessage();
                $type = 'error';
                activity()->causedBy(auth()->user())->useLog('Add OID')->log($msg);
            }
        }
        if($type == 'success'){
            session()->flash('success', $msg);
            return view('snmp.addont', compact('title'));
        }else{
            session()->flash('error', $msg);
            return view('snmp.addont', compact('title'));
        }
    }

    /**
     * Reboot ONT via SNMP
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rebootont(Request $request)
    {
        $oid = $request->input('oid');
        $user = $request->input('user', 'unknown');
        $ont = $request->input('ont', 'unknown');
        
        // Use the helper method to set SNMP value (2 for reboot)
        $newoid = $this->oidRegist . "." . $oid;
        $success = $this->snmpSet($newoid, 'i', 2, SNMP::VERSION_2C);
        
        if ($success) {
            $message = "ONT {$ont} ({$oid}) successfully rebooted for user {$user}";
            $this->logActivity($message);
            
            return response()->json([
                'success' => true,
                'oid' => $oid
            ]);
        }
        
        $message = "ONT {$ont} ({$oid}) reboot failed for user {$user}";
        $this->logActivity($message);
        
        return response()->json([
            'success' => false,
            'oid' => $oid,
            'message' => 'SNMP operation failed'
        ]);
    }

    public function rebootontapi(Request $request)
    {
        $oid = $request->input('oid');
        
        // Use the helper method to set SNMP value (2 for reboot)
        $newoid = $this->oidRegist . "." . $oid;
        $success = $this->snmpSet($newoid, 'i', 2, SNMP::VERSION_2C);
        
        if ($success) {
            $message = "ONT{$oid} successfully rebooted successfully";
            $this->logActivityapi($message);
            
            return response()->json(['success' => true, 'message' => 'ONT Rebooted successfully']);
        }
        
        $message = "ONT {$oid} reboot failed";
        $this->logActivityapi($message);
        
        return response()->json(['success' => false, 'message' => 'ONT Reboot failed']);
    }

    public function test(){
        $oid ='3.0';
        $onteth = "1.3.6.1.4.1.11863.6.100.1.7.2.1.37." . $oid;
        snmp_set_quick_print(1);
        // $newoid = env('SNMP_OID_ETH',null) .".". $oid;
        try
        {
            $session = new SNMP(SNMP::VERSION_2C, env('OLT_IP',null), "public");
            $session->set($onteth,'i',2);

            // $session = new SNMP(SNMP::VERSION_1, env('OLT_IP',null), "public");
            // $onteth = $session->get('1.3.6.1.4.1.11863.6.100.1.7.2.1.5.3.3');
            $session->close();

            // $newonteth = trim($onteth, '"');
        }
        catch(Exception $e)
        {
            if ($e->getMessage()){
                $onteth = $e->getMessage();
            }
        }
        return $onteth;

        // $ops = DB::table('opticalpowers')
        //     ->where('oid',$oid)
        //     ->first();
        //      $nm = $ops->name;
        //     return $nm;

            // $maxValue = DB::table('opticalpowers')->orderBy('updated_at', 'ASC')->first();

            // return $maxValue->updated_at;
    }

}

//1.3.6.1.4.1.11863.6.100.1.7.2.1.3 for Names
//1.3.6.1.4.1.11863.6.100.1.7.2.1.22 for opticalpower

//serial no = 1.3.6.1.4.1.11863.6.100.1.7.2.1.4
//uptime = 1.3.6.1.4.1.11863.6.100.1.7.2.1.15
//ethernet count = 1.3.6.1.4.1.11863.6.100.1.7.2.1.18
//temp = 1.3.6.1.4.1.11863.6.100.1.7.2.1.26
//Model = 1.3.6.1.4.1.11863.6.100.1.7.2.1.12
//setname=1.3.6.1.4.1.11863.6.100.1.7.2.1.3
//deactivate = 1.3.6.1.4.1.11863.6.100.1.7.2.1.35 set 0
//activate = 1.3.6.1.4.1.11863.6.100.1.7.2.1.35 set 1

//distance 1.3.6.1.4.1.11863.6.100.1.7.2.1.16 in meter

//line profile 1.3.6.1.4.1.11863.6.100.1.6.2.1.11


//   $session = new SNMP(SNMP::VERSION_2C, "127.0.0.1", "private");
//   $session->set('SNMPv2-MIB::sysContact.0', 's', "Nobody");

//1.3.6.1.4.1.11863.6.100.1.7.2.1.1.1.0


// .1.3.6.1.4.1.161.19.3.3.3.2.0

//names 1.3.6.1.4.1.11863.6.100.1.6.2.1.14

//deregister 1.3.6.1.4.1.11863.6.100.1.7.2.1.37 0
//register 1.3.6.1.4.1.11863.6.100.1.7.2.1.37 1

//optical status 1.3.6.1.4.1.11863.6.100.1.7.2.1.38

//Online ONTs per Pon
// 11-10-2024 06:56:51 (3588 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114689 = "107" [ASN_INTEGER]
// 11-10-2024 06:56:51 (3714 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114690 = "64" [ASN_INTEGER]
// 11-10-2024 06:56:51 (3838 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114691 = "0" [ASN_INTEGER]
// 11-10-2024 06:56:51 (3968 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114692 = "12" [ASN_INTEGER]
// 11-10-2024 06:56:51 (3982 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114693 = "0" [ASN_INTEGER]
// 11-10-2024 06:56:51 (4100 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114694 = "61" [ASN_INTEGER]
// 11-10-2024 06:56:51 (4242 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114695 = "43" [ASN_INTEGER]
// 11-10-2024 06:56:52 (4367 ms) : 1.3.6.1.4.1.11863.6.100.1.1.2.1.3.114696 = "8" [ASN_INTEGER]