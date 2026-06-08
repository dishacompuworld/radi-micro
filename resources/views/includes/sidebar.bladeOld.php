
<!-- Sidebar -->
@php
	if(isset($_GET['search'])){$search = $_GET['search'];}else{$search = "";}
	if(isset($_GET['name'])){$name = $_GET['name'];}else{$name = "";}
	if(isset($_GET['optionselect'])){$optionselect = $_GET['optionselect'];}else{$optionselect = "";}

	if($search){
		$searchvalue=$search;
	}else {
		$searchvalue=$name;
	}
	
	// echo $searchvalue;
@endphp

<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			<ul>
				<li>
					<form class="forms-sample" onSubmit="return setFormAction(event)" method="get" id="actionf">
					<div class="input-group input-group-sm mb-3">
						<select id="optionselect" class="selectpicker form-control">
							@if ($optionselect=='allactivenew')
								<option value="{{ url('/pppoe/allactivenew') }}" selected>Microtik</option>
							@else
								<option value="{{ url('/pppoe/allactivenew') }}">Microtik</option>
							@endif

							@if ($optionselect=='searchsubscriberall')
								<option value="{{ url('/searchsubscriberall') }}" selected>Radius</option>
							@else
								<option value="{{ url('/searchsubscriberall') }}">Radius</option>
							@endif

							@if ($optionselect=='showopticalpowers')
								<option value="{{ url('/showopticalpowers') }}" selected>Optical Power</option>
							@else
								<option value="{{ url('/showopticalpowers') }}">Optical Power</option>
							@endif
							
							@can('view-admin-radius-logs')
							@if ($optionselect=='adminaccessrequest')
								<option value="{{ url('/adminaccessrequest') }}" selected>Access Requests</option>
							@else
								<option value="{{ url('/adminaccessrequest') }}">Access Requests</option>
							@endif	
							@endcan
						</select>
						<input type="search" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="inputGroup-sizing-sm" id="search" value="{{ $searchvalue }}">
						<div class="input-group-append">
							<input type="submit" class="btn btn-outline-secondary" value="GO">
						</div>
					  </div>
					</form>
				</li>
				{{-- <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"> --}}
                    <li class="">
					{{-- <a href="{{route('dashboard')}}"><i class="fe fe-home"></i> <span>Dashboard</span></a> --}}
                    <a href=""><i class="fe fe-home"></i> <span>Dashboard</span></a>
				</li>

				{{-- @can('view-server') --}}
				<li class="submenu">
					<a href="#"><i class="bi bi-server"></i> <span> Microtik</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						{{-- @can('add-server') --}}
						<li><a class="{{ request()->routeIs('server.create') ? 'active' : '' }}" href="{{ route('server.create')}}">Add Microtik</a></li>
						{{-- @endcan --}}
						<li><a class="{{ request()->routeIs('server.index') ? 'active' : '' }}" href="{{ route('server.index')}}">Microtiks</a></li>
						{{-- @can('view-serverstats') --}}
						<li><a class="{{ request()->routeIs('stats.index') ? 'active' : '' }}" href="{{ route('stats.index')}}">Stats</a></li>
						{{-- @endcan
						@can('view-system-health') --}}
						<li><a class="{{ request()->routeIs('microtik.system.health') ? 'active' : '' }}" href="{{ route('microtik.system.health')}}">System Health</a></li>
						{{-- @endcan
						@can('view-neighbors') --}}
						<li><a class="{{ request()->routeIs('microtik.ip.neighbors') ? 'active' : '' }}" href="{{ route('microtik.ip.neighbors')}}">Neighbours</a></li>
						{{-- @endcan --}}
                        <li><a class="{{ request()->routeIs('pppoe.allactivenew') ? 'active' : '' }}" href="{{ route('pppoe.allactivenew')}}">Active Users</a></li>
                        {{-- @can('view-sheduler') --}}
						{{-- <li><a class="{{ request()->routeIs('microtik.ppp.traffice') ? 'active' : '' }}" href="{{ route('microtik.ppp.traffice')}}">User Live Traffic</a></li> --}}
                        {{-- @endcan --}}
						{{-- @can('view-sheduler') --}}
						<li><a class="{{ request()->routeIs('shedule.show') ? 'active' : '' }}" href="{{ route('shedule.show')}}">Sheduler</a></li>
                        {{-- @endcan
                        @can('view-script') --}}
						<li><a class="{{ request()->routeIs('microtik.scripts') ? 'active' : '' }}" href="{{ route('microtik.scripts')}}">Script</a></li>
                        {{-- @endcan
						@can('view-services') --}}
						<li><a class="{{ request()->routeIs('show.services') ? 'active' : '' }}" href="{{ route('show.services')}}">Services</a></li>
                        {{-- @endcan
						@can('view-microtik-logs') --}}
						<li><a class="{{ request()->routeIs('microtik.log') ? 'active' : '' }}" href="{{ route('microtik.log')}}">Logs</a></li>
                        {{-- @endcan
						@can('view-microtik-history') --}}
						<li><a class="{{ request()->routeIs('system.history') ? 'active' : '' }}" href="{{ route('system.history')}}">History</a></li>
                        {{-- @endcan
						@can('run-command') --}}
						<li><a class="{{ request()->routeIs('view.command') ? 'active' : '' }}" href="{{ route('view.command')}}">Terminal</a></li>
                        {{-- @endcan --}}
					</ul>
				</li>
				{{-- @endcan --}}

				{{-- @can('view-radius') --}}
				<li class="submenu">
					<a href="#"><i class="bi bi-database-fill"></i> <span>Radius</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						{{-- @can('view-subscriber') --}}
                        <li><a class="{{ request()->routeIs('search.subscriber') ? 'active' : '' }}" href="{{ route('search.subscriber')}}">Search Subscriber</a></li>
						{{-- @endcan --}}
						<li><a class="{{ request()->routeIs('search.subscriberall') ? 'active' : '' }}" href="{{ route('search.subscriberall')}}">Search All Location</a></li>
                        <li><a class="{{ request()->routeIs('show.onlineusers') ? 'active' : '' }}" href="{{ route('show.onlineusers')}}">Online Users</a></li>
						{{-- @can('view-locations') --}}
						    <li><a class="{{ request()->routeIs('location.show') ? 'active' : '' }}" href="{{ route('location.show')}}">Locations</a></li>
						{{-- @endcan --}}
                        	<li><a class="{{ request()->routeIs('locationdetails.show') ? 'active' : '' }}" href="{{ route('locationdetails.show')}}">Location Details</a></li>
                        {{-- @can('view-subscriber')
							<li><a class="{{ request()->routeIs('subscriber.show') ? 'active' : '' }}" href="{{ route('subscriber.show')}}">Subscribers</a></li>
						@endcan --}}
                        {{-- @can('view-packages') --}}
							<li><a class="{{ request()->routeIs('packages.show') ? 'active' : '' }}" href="{{ route('packages.show')}}">Packages</a></li>
						{{-- @endcan
                        @can('view-radius-logs') --}}
                        	<li><a class="{{ request()->routeIs('show.accesslogs') ? 'active' : '' }}" href="{{ route('show.accesslogs')}}">Access Request Logs</a></li>
                        {{-- @endcan
                        @can('view-admin-radius-logs') --}}
                        	<li><a class="{{ request()->routeIs('admin.accesslogs') ? 'active' : '' }}" href="{{ route('admin.accesslogs')}}">Admin access Logs</a></li>
                        {{-- @endcan --}}
					</ul>
				</li>
				{{-- @endcan --}}

				<li class="submenu">
					<a href="#"><i class="fe fe-lock"></i> <span>PRTG</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						{{-- <li><a class="{{ request()->routeIs('live.graph') ? 'active' : '' }}" href="{{route('live.graph')}}">Live Traffic</a></li> --}}
						{{-- <li><a class="{{ request()->routeIs('history.graph') ? 'active' : '' }}" href="{{route('history.graph')}}">All Traffic</a></li> --}}
						{{-- <li><a class="{{ request()->routeIs('all.sensors') ? 'active' : '' }}" href="{{route('all.sensors')}}">All Sensors</a></li>
						<li><a class="{{ request()->routeIs('ptrg.messsages') ? 'active' : '' }}" href="{{route('ptrg.messsages')}}">Messages</a></li> --}}
					</ul>
				</li>

				{{-- @can('show-op-power') --}}
                <li class="submenu">
					<a href="#"><i class="bi bi-optical-audio-fill"></i> <span>OLT</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						<li><a class="{{ request()->routeIs('show.opticalpowers') ? 'active' : '' }}" href="{{route('show.opticalpowers')}}">TP-link OpticalPwr</a></li>
						{{-- @can('add-ont') --}}
						<li><a class="{{ request()->routeIs('add.ont') ? 'active' : '' }}" href="{{route('add.ont')}}">Add ONT</a></li>
						{{-- @endcan
                        @can('rename-ont') --}}
                        <li><a class="{{ request()->routeIs('edit.ont') ? 'active' : '' }}" href="{{route('edit.ont')}}">Rename ONT</a></li>
                        {{-- @endcan --}}
					</ul>
				</li>
				{{-- @endcan

				@can('send-whatsapp') --}}
				<li class="{{ request()->routeIs('whatsapp.msg') ? 'active' : '' }}">
					<a href="{{route('whatsapp.msg')}}"><i class="bi bi-whatsapp"></i> <span>Send Message</span></a>
				</li>
				{{-- @endcan --}}

                {{-- <li class="{{ request()->routeIs('find.mac.vendor ') ? 'active' : '' }}"> --}}
                    <li class="">
					{{-- <a href="{{route('find.mac.vendor')}}"><i class="bi bi-ethernet"></i> <span>Find MAC Details</span></a> --}}
                    <a href=""><i class="bi bi-ethernet"></i> <span>Find MAC Details</span></a>
				</li>

				{{-- @can('view-access-control') --}}
				<li class="submenu">
					<a href="#"><i class="bi bi-controller"></i> <span> Access Control</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						{{-- @can('view-permission') --}}
						<li><a class="{{ request()->routeIs('permissions.index') ? 'active' : '' }}" href="{{route('permissions.index')}}">Permissions</a></li>
						{{-- @endcan
						@can('view-role') --}}
						<li><a class="{{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{route('roles.index')}}">Roles</a></li>
						{{-- @endcan --}}
					</ul>
				</li>
				{{-- @endcan

				@can('view-users') --}}
				<li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
					<a href="{{route('users.index')}}"><i class="bi bi-people-fill"></i> <span>Users</span></a>
				</li>
				{{-- @endcan --}}

				{{-- <li class="{{ request()->routeIs('profile') ? 'active' : '' }}">
					<a href="{{route('profile')}}"><i class="bi bi-person-lines-fill"></i> <span>Profile</span></a>
				</li> --}}

                <li class="">
					<a href=""><i class="bi bi-person-lines-fill"></i> <span>Profile</span></a>
				</li>

				{{-- @can('show-log') --}}
                <li class="submenu">
					<a href="#"><i class="bi bi-clipboard-check-fill"></i> <span>Logs</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						    <li><a class="{{ request()->routeIs('show.log') ? 'active' : '' }}" href="{{ route('show.log')}}">Show Logs</a></li>
                        {{-- @can('all-logs') --}}
						    <li><a class="{{ request()->routeIs('show.alllogs') ? 'active' : '' }}" href="{{ route('show.alllogs')}}">All Logs</a></li>
						{{-- @endcan --}}
					</ul>
				</li>
				{{-- @endcan

				@can('show-alerts') --}}
                <li class="{{ request()->routeIs('alert.index') ? 'active' : '' }}">
					<a href="{{route('alert.index')}}"><i class="bi bi-people-fill"></i> <span>Alert Messages</span></a>
				</li>
				{{-- @endcan

				@can('view-settings') --}}
				<li class="{{ request()->routeIs('settings') ? 'active' : '' }}">
					<a href="{{route('settings')}}">
						<i class="bi bi-sliders2"></i>
						 <span> Settings</span>
					</a>
				</li>
				{{-- @endcan --}}
			</ul>
		</div>
	</div>
</div>
<!-- /Sidebar -->
