<!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{route('dashboard')}}" class="app-brand-link">
              <img src="{{asset('assets/img/dishcompuworldlogo.png')}}" alt="Logo" class="app-brand-logo demo" />
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{route('dashboard')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>

            @php
                $microtikRoutes = [
                    'server.create', 'server.index', 'stats.index', 'microtik.system.health',
                    'microtik.ip.neighbors', 'pppoe.allactivenew', 'shedule.show', 'microtik.scripts',
                    'show.services', 'microtik.log', 'system.history', 'view.command'
                ];
                $isMicrotikActive = collect($microtikRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp
            <!-- Microtik -->
            <li class="menu-item {{ $isMicrotikActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-server"></i>
                <div data-i18n="Microtik">Microtik</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('server.create') ? 'active' : '' }}">
                  <a href="{{ route('server.create')}}" class="menu-link">
                    <div data-i18n="Add Microtik">Add Microtik</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('server.index') ? 'active' : '' }}">
                  <a href="{{ route('server.index')}}" class="menu-link">
                    <div data-i18n="Microtiks">Microtiks</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('stats.index') ? 'active' : '' }}">
                  <a href="{{ route('stats.index')}}" class="menu-link">
                    <div data-i18n="Statistics">Statistics</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('microtik.system.health') ? 'active' : '' }}">
                  <a href="{{ route('microtik.system.health')}}" class="menu-link">
                    <div data-i18n="System Health">System Health</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('microtik.ip.neighbors') ? 'active' : '' }}">
                  <a href="{{ route('microtik.ip.neighbors')}}" class="menu-link">
                    <div data-i18n="Neighbours">Neighbours</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('pppoe.allactivenew') ? 'active' : '' }}">
                  <a href="{{ route('pppoe.allactivenew')}}" class="menu-link">
                    <div data-i18n="Active Users">Active Users</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shedule.show') ? 'active' : '' }}">
                  <a href="{{ route('shedule.show')}}" class="menu-link">
                    <div data-i18n="Sheduler">Sheduler</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('microtik.scripts') ? 'active' : '' }}">
                  <a href="{{ route('microtik.scripts')}}" class="menu-link">
                    <div data-i18n="Script">Script</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('show.services') ? 'active' : '' }}">
                  <a href="{{ route('show.services')}}" class="menu-link">
                    <div data-i18n="Services">Services</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('microtik.log') ? 'active' : '' }}">
                  <a href="{{ route('microtik.log')}}" class="menu-link">
                    <div data-i18n="Logs">Logs</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system.history') ? 'active' : '' }}">
                  <a href="{{ route('system.history')}}" class="menu-link">
                    <div data-i18n="History">History</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('view.command') ? 'active' : '' }}">
                  <a href="{{ route('view.command')}}" class="menu-link">
                    <div data-i18n="Terminal">Terminal</div>
                  </a>
                </li>
              </ul>
            </li>


            @php
                $radiusRoutes = [
                    'search.subscriber', 'search.subscriberall', 'show.onlineusers', 'location.show',
                    'locationdetails.show', 'packages.show', 'show.accesslogs', 'admin.accesslogs'
                ];
                $isRadiusActive = collect($radiusRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp

            
            <!-- Radius -->
            <li class="menu-item {{ $isRadiusActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wifi"></i>
                <div data-i18n="Radius">Radius</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('search.subscriber') ? 'active' : '' }}">
                  <a href="{{ route('search.subscriber')}}" class="menu-link">
                    <div data-i18n="Search Suscribers">Search Suscribers</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('search.subscriberall') ? 'active' : '' }}">
                  <a href="{{ route('search.subscriberall')}}" class="menu-link">
                    <div data-i18n="Search All Location">Search All Location</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('show.onlineusers') ? 'active' : '' }}">
                  <a href="{{ route('show.onlineusers')}}" class="menu-link">
                    <div data-i18n="Online Users">Online Users</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('location.show') ? 'active' : '' }}">
                  <a href="{{ route('location.show')}}" class="menu-link">
                    <div data-i18n="Locations">Locations</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('locationdetails.show') ? 'active' : '' }}">
                  <a href="{{ route('locationdetails.show')}}" class="menu-link">
                    <div data-i18n="Location Details">Location Details</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('packages.show') ? 'active' : '' }}">
                  <a href="{{ route('packages.show')}}" class="menu-link">
                    <div data-i18n="Packages">Packages</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('show.accesslogs') ? 'active' : '' }}">
                  <a href="{{ route('show.accesslogs')}}" class="menu-link">
                    <div data-i18n="Access Request Logs">Access Request Logs</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.accesslogs') ? 'active' : '' }}">
                  <a href="{{ route('admin.accesslogs')}}" class="menu-link">
                    <div data-i18n="Admin Access Logs">Admin Access Logs</div>
                  </a>
                </li>
              </ul>
            </li>

            @php
                $prtgRoutes = [
                    'live.graph.page', 'history.graph', 'all.sensors', 'ptrg.messsages'
                ];
                $isPrtgActive = collect($prtgRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp


            <!-- PRTG -->
            <li class="menu-item {{ $isPrtgActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div data-i18n="PRTG">PRTG</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('live.graph.page') ? 'active' : '' }}">
                  <a href="{{ route('live.graph.page')}}" class="menu-link">
                    <div data-i18n="Live Traffice">Live Traffice</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('history.graph') ? 'active' : '' }}">
                  <a href="{{ route('history.graph')}}" class="menu-link">
                    <div data-i18n="All Traffice">All Traffice</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('all.sensors') ? 'active' : '' }}">
                  <a href="{{ route('all.sensors')}}" class="menu-link">
                    <div data-i18n="All Sensors">All Sensors</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('ptrg.messsages') ? 'active' : '' }}">
                  <a href="{{ route('ptrg.messsages')}}" class="menu-link">
                    <div data-i18n="Messages">Messages</div>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Suscribers -->

            @php
                $oltRoutes = [
                    'show.opticalpowers', 'add.ont', 'edit.ont'
                ];
                $isOltActive = collect($oltRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp

            
            <!-- OLT -->
            <li class="menu-item {{ $isOltActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-network-chart"></i>
                <div data-i18n="OLT">OLT</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('show.opticalpowers') ? 'active' : '' }}">
                  <a href="{{ route('show.opticalpowers')}}" class="menu-link">
                    <div data-i18n="Olt Powers">Olt Powers</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('add.ont') ? 'active' : '' }}">
                  <a href="{{ route('add.ont')}}" class="menu-link">
                    <div data-i18n="Add ONT">Add ONT</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('edit.ont') ? 'active' : '' }}">
                  <a href="{{ route('edit.ont')}}" class="menu-link">
                    <div data-i18n="Edit ONT">Edit ONT</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Utilities -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Utilities</span></li>
            <!-- Find Mac -->
            <li class="menu-item {{ request()->routeIs('find.mac.vendor') ? 'active' : '' }}">
              <a href="{{ route('find.mac.vendor')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-search"></i>
                <div data-i18n="Find Mac Details">Find Mac Details</div>
              </a>
            </li>

            <li class="menu-item {{ request()->routeIs('whatsapp.msg') ? 'active' : '' }}">
              <a href="{{ route('whatsapp.msg')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-message"></i>
                <div data-i18n="Send Message">Send Message</div>
              </a>
            </li>

            @php
                $adminRoutes = [
                    'permissions.index', 'roles.index'
                ];
                $isAdminActive = collect($adminRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp

            <!-- Admin -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Admin</span>
            </li>
            <li class="menu-item {{ $isAdminActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div data-i18n="OLT">Access Control</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('permissions.index') ? 'active' : '' }}">
                  <a href="{{ route('permissions.index')}}" class="menu-link">
                    <div data-i18n="Permissions">Permissions</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                  <a href="{{ route('roles.index')}}" class="menu-link">
                    <div data-i18n="Roles">Roles</div>
                  </a>
                </li>
              </ul>
            </li>

            <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
              <a href="{{ route('users.index')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Users">Users</div>
              </a>
            </li>

            <li class="menu-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
              <a href="{{ route('profile.edit')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Users">Profile</div>
              </a>
            </li>

            @php
                $logsRoutes = [
                    'show.log', 'show.alllogs'
                ];
                $isLogsActive = collect($logsRoutes)->contains(fn($route) => request()->routeIs($route));
            @endphp

            <li class="menu-item {{ $isLogsActive ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-history"></i>
                <div data-i18n="Logs">Logs</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('show.log') ? 'active' : '' }}">
                  <a href="{{ route('show.log')}}" class="menu-link">
                    <div data-i18n="Show Logs">Show Logs</div>
                  </a>
                </li>

                <li class="menu-item {{ request()->routeIs('show.alllogs') ? 'active' : '' }}">
                  <a href="{{ route('show.alllogs')}}" class="menu-link">
                    <div data-i18n="All Logs">All Logs</div>
                  </a>
                </li>
              </ul>
            </li>

            <li class="menu-item {{ request()->routeIs('alert.index') ? 'active' : '' }}">
              <a href="{{ route('alert.index')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div data-i18n="Alert Messages">Alert Messages</div>
              </a>
            </li>

            <li class="menu-item {{ request()->routeIs('settings') ? 'active' : '' }}">
              <a href="{{ route('settings')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Settings">Settings</div>
              </a>
            </li>
            <!-- Admin -->
          </ul>
        </aside>
        <!-- / Menu -->