<?php

namespace App\Http\Controllers;

use RouterOS\Client;
use RouterOS\Config;
use App\Models\Server;
use App\Models\RouterosAPI as LegacyRouterosAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class PPPoEUserController extends Controller
{
    private ?Client $client = null;
    private int $connectionTimeout = 5;

    /**
     * Connect to a MikroTik server using evilfreelancer RouterOS client
     *
     * @param int $serverId
     * @return Server
     * @throws \Exception
     */
    private function connectToServer($serverId)
    {
        $server = Server::find($serverId);

        if (!$server) {
            throw new \Exception("Server with ID {$serverId} not found");
        }

        $config = (new Config())
            ->set('host', $server->mip)
            ->set('user', $server->username)
            ->set('pass', $server->password)
            ->set('port', $server->port ?? 8728)
            ->set('timeout', $this->connectionTimeout)
            ->set('ssl', false);

        $this->client = new Client($config);

        return $server;
    }

    /**
     * Execute API command with error handling
     * Supports a simple query without params or equality filters when params provided
     */
    private function executeCommand(string $command, array $params = [])
    {
        if (!$this->client) {
            throw new \Exception('No RouterOS client available');
        }

        // Simple query
        if (empty($params)) {
            return $this->client->query($command)->read();
        }

        $q = $this->client->query($command);
        foreach ($params as $k => $v) {
            if (method_exists($q, 'equal')) {
                $q->equal($k, $v);
            }
        }

        return $q->read();
    }

    /**
     * Log message to MikroTik
     */
    private function logToMikrotik(string $message): void
    {
        if (!$this->client) {
            return;
        }

        try {
            if (method_exists($this->client, 'query')) {
                // Attempt to send a log entry; use a best-effort approach
                $this->client->query('/log/error')->equal('message', $message)->read();
            }
        } catch (\Throwable $e) {
            Log::warning('Error writing log to MikroTik: ' . $e->getMessage());
        }
    }

    /**
     * Safely disconnect the current RouterOS client if present
     */
    private function disconnectClient(): void
    {
        if (!$this->client) {
            return;
        }

        try {
            if (method_exists($this->client, 'disconnect')) {
                $this->client->disconnect();
            } elseif (method_exists($this->client, 'close')) {
                $this->client->close();
            }
        } catch (\Throwable $e) {
            Log::warning('Error disconnecting RouterOS client: ' . $e->getMessage());
        }

        $this->client = null;
    }

    /**
     * Fetch active PPPoE users from all enabled MikroTik servers.
     */
    public function getActiveUsers(): array
    {
        $servers = Server::where('enable', '1')->get();
        $active = [];

        foreach ($servers as $srv) {
            try {
                $this->connectToServer($srv->id);

                $message = (Auth::check() ? Auth::user()->name : 'system') . ' checking active users.';
                $this->logToMikrotik($message);

                $response = $this->executeCommand('/ppp/active/print');

                if (is_array($response)) {
                    foreach ($response as $row) {
                        $active[] = [
                            'server_id'   => $srv->id,
                            'server_name' => $srv->name,
                            'shortname'   => $srv->shortname ?? null,
                            'user'        => $row['name'] ?? null,
                            'address'     => $row['address'] ?? null,
                            'caller_id'   => $row['caller-id'] ?? null,
                            'uptime'      => $row['uptime'] ?? null,
                            'encoding'    => $row['encoding'] ?? null,
                            'service'     => $row['service'] ?? null,
                            'via_ip'      => $srv->mip,
                            '.id'         => $row['.id'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::error('PPPoE fetch failed for server ' . ($srv->id ?? 'unknown') . ': ' . $e->getMessage());
            } finally {
                $this->disconnectClient();
            }
        }

        return $active;
    }

    /**
     * JSON API output
     */
    public function json(Request $request)
    {
        $data = $this->getActiveUsers();

        return response()->json([
            'status' => 'ok',
            'count'  => count($data),
            'data'   => $data,
        ]);
    }

    public function delet(Request $request)
    {
        try {
            $server = $this->connectToServer($request->get('server'));

            $this->executeCommand('/ppp/active/remove', ['.id' => $request->id]);

            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users')
                ->log('PPPoE User ' . $request->cname . ' Deleted from ' . $server->name);

            return response()->json([
                'success' => true,
                'cname'   => $request->cname,
                'server'  => $server->name,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in delet method: ' . $e->getMessage());
            return response()->json(['success' => false, 'cname' => $request->cname, 'error' => $e->getMessage()]);
        } finally {
            $this->disconnectClient();
        }
    }

    public function deletapi(Request $request)
    {
        try {
            $server = $this->connectToServer($request->get('server'));

            $this->executeCommand('/ppp/active/remove', ['.id' => $request->id]);

            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users - API')
                ->log('PPPoE User ' . $request->cname . ' Deleted from ' . $server->name);

            return response()->json([
                'success' => true,
                'cname'   => $request->cname,
                'server'  => $server->name,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in deletapi method: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users - API')
                ->log('PPPoE User ' . $request->cname . ' Deletion Failed');

            return response()->json(['success' => false, 'cname' => $request->cname, 'error' => $e->getMessage()]);
        } finally {
            $this->disconnectClient();
        }
    }

    public function realTimePing(Request $request)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        try {
            $server = $this->connectToServer($request->get('server'));
            $username = $request->get('username', 'unknown');
            $ip = $request->get('ip');
            $pingCount = min(intval($request->get('time', 5)), 30);

            $message = auth()->user()->name . ' checking ping for user ' . $username . '.';
            $this->logToMikrotik($message);

            $iterations = 0;
            while ($iterations < $pingCount) {
                $pingResult = $this->executeCommand('/ping', ['address' => $ip, 'count' => 1]);

                if ($pingResult) {
                    echo 'data: ' . json_encode($pingResult) . "\n\n";
                    ob_flush();
                    flush();
                }

                $iterations++;
            }
        } catch (\Throwable $e) {
            Log::error('Error in realTimePing: ' . $e->getMessage());
            echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
            ob_flush();
            flush();
        } finally {
            $this->disconnectClient();
        }
    }

    public function realTimePingApi(Request $request)
    {
        $server = $this->connectToServer($request->get('server'));
        $username = $request->get('username', 'unknown');
        $ip = $request->get('ip');
        $pingCount = min(intval($request->get('time', 5)), 30);

        $message = auth()->user()->name . ' checking ping for user ' . $username . ' from API';
        activity()->causedBy(auth()->user())->useLog('Ping - api')->log('Checking ping for user '. $username . ' of ' . $ip);
        $this->logToMikrotik($message);

        $pingResult = $this->executeCommand('/ping', ['address' => $ip, 'count' => 1]);

        if ($pingResult) {
            $this->disconnectClient();
            return response()->json($pingResult[0] ?? $pingResult, 200);
        }

        $this->disconnectClient();
        return response()->json(['error' => 'No result'], 500);
    }

    /**
     * Convert seconds to a human-readable format
     */
    private function convertSeconds($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $result = '';

        if ($days > 0) {
            $result .= $days . 'day ';
        }

        if ($hours > 0) {
            $result .= $hours . 'hr ';
        }

        if ($minutes > 0) {
            $result .= $minutes . 'min ';
        }

        if ($seconds > 0 || $result === '') {
            $result .= $seconds . 'sec';
        }

        return trim($result);
    }

    /**
     * Parse RouterOS uptime strings like 1w2d3h4m5s into seconds.
     */
    private function parseUptimeToSeconds(?string $uptime): int
    {
        if (!$uptime) {
            return 0;
        }

        $seconds = 0;
        preg_match_all('/(\d+)([wdhms])/', $uptime, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $value = (int) $match[1];
            $unit = $match[2];

            switch ($unit) {
                case 'w':
                    $seconds += $value * 7 * 24 * 60 * 60;
                    break;
                case 'd':
                    $seconds += $value * 24 * 60 * 60;
                    break;
                case 'h':
                    $seconds += $value * 60 * 60;
                    break;
                case 'm':
                    $seconds += $value * 60;
                    break;
                case 's':
                    $seconds += $value;
                    break;
            }
        }

        return $seconds;
    }

    public function allactivenew(Request $request)
    {
        $title = 'All Active PPPoE Users';
        $servers = Server::where('enable', '1')->get();
        $checked = $request->boolean('checked') ? 1 : 0;
        $search = $request->get('name');

        $isDataTableRequest = $request->ajax()
            || $request->hasAny(['draw', 'columns', 'order', 'start', 'length', 'search'])
            || $request->wantsJson();

        if ($isDataTableRequest) {
            $finalarray = [];

            foreach ($servers as $server) {
                try {
                    $this->connectToServer($server->id);

                    $message = (Auth::check() ? Auth::user()->name : 'system') . ' checking active users.';
                    $this->logToMikrotik($message);

                    $rows = [];
                    foreach ([
                        '/ppp/active/print',
                        'ppp/active/print',
                        '/ppp/active',
                        'ppp/active',
                    ] as $command) {
                        try {
                            $result = $this->executeCommand($command);
                            if (is_array($result) && !empty($result)) {
                                $rows = $result;
                                break;
                            }
                        } catch (\Throwable $e) {
                            Log::warning('PPPoE query failed for ' . $command . ' on server ' . $server->id . ': ' . $e->getMessage());
                        }
                    }

                    if (empty($rows)) {
                        try {
                            $legacy = new LegacyRouterosAPI();
                            if ($legacy->connect($server->mip, $server->username, $server->password)) {
                                $legacyRows = $legacy->comm('/ppp/active/print');
                                if (is_array($legacyRows) && !empty($legacyRows)) {
                                    $rows = $legacyRows;
                                }
                                $legacy->disconnect();
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Legacy PPPoE fetch failed for server ' . $server->id . ': ' . $e->getMessage());
                        }
                    }

                    if (!is_array($rows)) {
                        continue;
                    }

                    $normalizedRows = [];
                    foreach ($rows as $row) {
                        if (is_array($row)) {
                            if (isset($row['name']) || isset($row['address']) || isset($row['.id'])) {
                                $normalizedRows[] = $row;
                            } elseif (array_is_list($row)) {
                                foreach ($row as $nestedRow) {
                                    if (is_array($nestedRow) && (isset($nestedRow['name']) || isset($nestedRow['address']) || isset($nestedRow['.id']))) {
                                        $normalizedRows[] = $nestedRow;
                                    } elseif (is_object($nestedRow)) {
                                        $normalizedRows[] = (array) $nestedRow;
                                    }
                                }
                            }
                        } elseif (is_object($row)) {
                            $normalizedRows[] = (array) $row;
                        }
                    }

                    foreach (array_reverse($normalizedRows) as $rowArray) {
                        if (!is_array($rowArray) && !is_object($rowArray)) {
                            continue;
                        }

                        $rowArray = is_array($rowArray) ? $rowArray : (array) $rowArray;
                        if (!isset($rowArray['name']) && !isset($rowArray['address']) && !isset($rowArray['.id'])) {
                            continue;
                        }

                        $uptime = $rowArray['uptime'] ?? '';

                        $finalarray[] = [
                            'server' => $server->name,
                            'serverid' => $server->id,
                            'name' => $rowArray['name'] ?? null,
                            '.id' => $rowArray['.id'] ?? null,
                            'address' => $rowArray['address'] ?? null,
                            'caller-id' => $rowArray['caller-id'] ?? null,
                            'uptime' => $uptime,
                            'time' => $this->parseUptimeToSeconds($uptime),
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::error('Error fetching PPPoE users for server ' . ($server->id ?? 'unknown') . ': ' . $e->getMessage());
                } finally {
                    $this->disconnectClient();
                }
            }

            // $finalarray = array_reverse($finalarray);

            return DataTables::of($finalarray)
                ->addIndexColumn()
                ->addColumn('namel', function ($data) {
                    $namee = $data['name'];
                    return '<a href="' . route('subscriber.microtik', ['name' => $namee]) . '">' . $namee . '</a>';
                })
                ->addColumn('addressnew', function ($data) {
                    $address = $data['address'] ?? '';
                    $target = 'http://' . $address . ':8080';
                    $class = str_starts_with($address, '10.') ? 'text-danger' : '';

                    return '<a href="' . $target . '" target="_new" class="' . $class . '">' . $address . '</a>';
                })
                ->addColumn('ping', function ($data) {
                    return '<a href="' . route('pppoe.ping', [
                        'ip' => $data['address'],
                        'username' => $data['name'],
                        'server' => $data['serverid'],
                    ]) . '">Ping</a>';
                })
                ->addColumn('remove', function ($data) {
                    $chartbtn = '<a href="' . route('traffic.chart', [
                        'serverId' => $data['serverid'],
                        'username' => $data['name'],
                    ]) . '" class="btn btn-success btn-sm">Live Graph</a>';

                    $removebtn = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" data-route="' . route('pppoe.delet') . '" data-cname="' . $data['name'] . '" data-server="' . $data['serverid'] . '" data-id="' . $data['.id'] . '" id="removebtn">Remove</a>';

                    return $chartbtn . ' ' . $removebtn;
                })
                ->addColumn('newmac', function ($data) {
                    return str_replace(':', '-', $data['caller-id'] ?? '');
                })
                ->addColumn('newuptime', function ($data) {
                    return $this->convertSeconds($data['time'] ?? 0);
                })
                ->rawColumns(['namel', 'remove', 'addressnew', 'ping', 'newmac', 'newuptime'])
                ->make(true);
        }

        activity()->causedBy(auth()->user())->useLog('Active Users')->log('All Active Users checked.');

        return view('PPPoE.newactivenew', compact('title', 'servers', 'checked', 'search'));
    }


    public function newactiveserver(Request $request)
    {
        $title = 'Active PPPoE Users';
        $iid = $request->get('server');
        $search = $request->get('name');
        $servers = Server::where('enable', '1')->get();
        $urll = $iid
            ? route('pppoe.newactive', ['server' => $iid])
            : route('pppoe.newactive');

        $serveriid = $iid ? Server::find($iid) : null;

        $isDataTableRequest = $request->ajax()
            || $request->hasAny(['draw', 'columns', 'order', 'start', 'length', 'search'])
            || $request->wantsJson();

        if ($isDataTableRequest) {
            if (!$serveriid) {
                return response()->json([
                    'data' => [],
                    'draw' => (int) $request->get('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            try {
                $this->connectToServer($serveriid->id);

                $message = (Auth::check() ? Auth::user()->name : 'system') . ' checking active users.';
                $this->logToMikrotik($message);

                $rows = [];
                foreach ([
                    '/ppp/active/print',
                    'ppp/active/print',
                    '/ppp/active',
                    'ppp/active',
                ] as $command) {
                    try {
                        $result = $this->executeCommand($command);
                        if (is_array($result) && !empty($result)) {
                            $rows = $result;
                            break;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('PPPoE query failed for ' . $command . ' on server ' . $serveriid->id . ': ' . $e->getMessage());
                    }
                }

                if (empty($rows)) {
                    try {
                        $legacy = new LegacyRouterosAPI();
                        if ($legacy->connect($serveriid->mip, $serveriid->username, $serveriid->password)) {
                            $legacyRows = $legacy->comm('/ppp/active/print');
                            if (is_array($legacyRows) && !empty($legacyRows)) {
                                $rows = $legacyRows;
                            }
                            $legacy->disconnect();
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Legacy PPPoE fetch failed for server ' . $serveriid->id . ': ' . $e->getMessage());
                    }
                }

                if (!is_array($rows)) {
                    $rows = [];
                }

                $finalarray = [];
                foreach (array_reverse($rows) as $row) {
                    if (!is_array($row) && !is_object($row)) {
                        continue;
                    }

                    $rowArray = is_array($row) ? $row : (array) $row;
                    if (!isset($rowArray['name']) && !isset($rowArray['address']) && !isset($rowArray['.id'])) {
                        continue;
                    }

                    $uptime = $rowArray['uptime'] ?? '';
                    $finalarray[] = [
                        'server' => $serveriid->name,
                        'serverid' => $serveriid->id,
                        'name' => $rowArray['name'] ?? null,
                        '.id' => $rowArray['.id'] ?? null,
                        'address' => $rowArray['address'] ?? null,
                        'caller-id' => $rowArray['caller-id'] ?? null,
                        'uptime' => $uptime,
                        'time' => $this->parseUptimeToSeconds($uptime),
                    ];
                }

                return DataTables::of($finalarray)
                    ->addIndexColumn()
                    ->addColumn('namel', function ($data) {
                        $namee = $data['name'] ?? '';
                        return '<a href="' . route('subscriber.microtik', ['name' => $namee]) . '">' . $namee . '</a>';
                    })
                    ->addColumn('ping', function ($data) use ($serveriid) {
                        return '<a href="' . route('pppoe.ping', [
                            'ip' => $data['address'] ?? '',
                            'username' => $data['name'] ?? '',
                            'server' => $serveriid->id,
                        ]) . '">Ping</a>';
                    })
                    ->addColumn('newmac', function ($data) {
                        return str_replace(':', '-', $data['caller-id'] ?? '');
                    })
                    ->addColumn('addressnew', function ($data) {
                        $address = $data['address'] ?? '';
                        $tenip = explode('.', $address);
                        $class = isset($tenip[0]) && $tenip[0] == '10' ? 'text-danger' : '';
                        $target = 'http://' . $address . ':8080';

                        return '<a href="' . $target . '" target="_new" class="' . $class . '">' . $address . '</a>';
                    })
                    ->addColumn('uptime', function ($data) {
                        return $data['uptime'] ?? '';
                    })
                    ->addColumn('remove', function ($data) use ($serveriid) {
                        $removebtn = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" data-route="' . route('pppoe.delet') . '" data-cname="' . ($data['name'] ?? '') . '" data-server="' . $serveriid->id . '" data-id="' . ($data['.id'] ?? '') . '" id="removebtn">Remove</a>';
                        $chartbtn = '<a href="' . route('traffic.chart', ['serverId' => $serveriid->id, 'username' => $data['name'] ?? '']) . '" class="btn btn-success btn-sm">Live Graph</a>';

                        return $chartbtn . ' ' . $removebtn;
                    })
                    ->rawColumns(['namel', 'ping', 'newmac', 'addressnew', 'uptime', 'remove'])
                    ->make(true);
            } catch (\Throwable $e) {
                Log::error('Error in newactiveserver for server ' . ($serveriid->id ?? 'unknown') . ': ' . $e->getMessage());
                return response()->json([
                    'data' => [],
                    'draw' => (int) $request->get('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                $this->disconnectClient();
            }
        }

        if ($serveriid) {
            activity()->causedBy(auth()->user())->useLog('Active Users')->log('Active users checked from ' . $serveriid->name);
        }

        return view('PPPoE.newactive', compact('title', 'iid', 'servers', 'search', 'urll'));
    }



    public function testactiveserver(Request $request)
    {
        $iid = $request->get('server');

        if (!$iid) {
            return response()->json([
                'error' => 'Missing server parameter',
                'server' => null,
                'count' => 0,
                'rows' => [],
            ], 400);
        }

        $serveriid = Server::find($iid);

        if (!$serveriid) {
            return response()->json([
                'error' => 'Server not found',
                'server' => $iid,
                'count' => 0,
                'rows' => [],
            ], 404);
        }

        try {
            $this->connectToServer($serveriid->id);

            $message = (Auth::check() ? Auth::user()->name : 'system') . ' checking active users.';
            $this->logToMikrotik($message);

            $rows = [];

            try {
                $legacy = new LegacyRouterosAPI();
                if ($legacy->connect($serveriid->mip, $serveriid->username, $serveriid->password)) {
                    $legacyRows = $legacy->comm('/ppp/active/print');
                    if (is_array($legacyRows) && !empty($legacyRows)) {
                        $rows = $legacyRows;
                    }
                    $legacy->disconnect();
                }
            } catch (\Throwable $e) {
                Log::warning('Legacy PPPoE fetch failed: ' . $e->getMessage());
            }

            if (!is_array($rows) || empty($rows)) {
                foreach ([
                    '/ppp/active/print',
                    'ppp/active/print',
                    '/ppp/active',
                    'ppp/active',
                ] as $command) {
                    try {
                        $result = $this->executeCommand($command);
                        if (is_array($result) && !empty($result)) {
                            $rows = $result;
                            break;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Primary PPPoE query failed for ' . $command . ': ' . $e->getMessage());
                    }
                }
            }

            if (!is_array($rows)) {
                $rows = [];
            }

            $normalizedRows = [];
            foreach ($rows as $row) {
                if (is_array($row)) {
                    if (isset($row['name']) || isset($row['address']) || isset($row['.id'])) {
                        $normalizedRows[] = $row;
                    } elseif (array_is_list($row)) {
                        foreach ($row as $nestedRow) {
                            if (is_array($nestedRow)) {
                                $normalizedRows[] = $nestedRow;
                            } elseif (is_object($nestedRow)) {
                                $normalizedRows[] = (array) $nestedRow;
                            }
                        }
                    }
                } elseif (is_object($row)) {
                    $normalizedRows[] = (array) $row;
                }
            }

            $normalizedRows = array_values(array_reverse($normalizedRows));

            return response()->json([
                'server' => $serveriid->name,
                'server_id' => $serveriid->id,
                'count' => count($normalizedRows),
                'rows' => $normalizedRows,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in testactiveserver for server ' . ($serveriid->id ?? 'unknown') . ': ' . $e->getMessage());

            return response()->json([
                'server' => $serveriid->name ?? null,
                'server_id' => $serveriid->id ?? null,
                'error' => $e->getMessage(),
                'count' => 0,
                'rows' => [],
            ], 500);
        } finally {
            $this->disconnectClient();
        }
    }


}
