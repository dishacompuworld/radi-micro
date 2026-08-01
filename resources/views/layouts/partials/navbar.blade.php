<!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center me-auto">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <li class="nav-item dropdown">
                        <a
                          class="nav-link dropdown-toggle"
                          href="javascript:void(0)"
                          id="navbarDropdown"
                          role="button"
                          data-bs-toggle="dropdown"
                          aria-expanded="false"
                        >
                          Microtik Users
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                          <li><a class="dropdown-item" href="javascript:void(0)">Microtik Users</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)">Radius Users</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)">Admin Access Requests</a></li>
                          <li>
                            <hr class="dropdown-divider" />
                          </li>
                          <li><a class="dropdown-item" href="javascript:void(0)">Optical Power</a></li>
                        </ul>
                      </li>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <!-- mseb container -->
              <ul class="nav user-menu me-2">
                <li class="nav-item">
                  <div class="temp-container {{ $bgClass ?? '' }}" id="temp-container">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-white-50">Main Server Temp.&nbsp;</span>
                      <span class="text-white mb-0 fw-bold" id="temp-text">{{ '0°C'}}</span>
                    </div>
                  </div>
                  <script>
                            // This script is placed directly after the mseb-container element
                            // so the elements are guaranteed to be available when the script runs.
                            const tempInfoContainer = document.getElementById('temp-container');
                            const tempStatusTextElement = document.getElementById('temp-text');

                            function updateTempInfoDisplay() {
                                // Check if elements exist before attempting to update them.
                                // This is a safeguard, though they should always be present in the header.
                                if (!tempInfoContainer || !tempStatusTextElement) {
                                    console.error('MSEB info elements not found in header. Clearing update interval.');
                                    // Clear the interval if elements are unexpectedly missing
                                    clearInterval(window.tempUpdateInterval);
                                    return;
                                }

                                fetch("{{ route('admin.dashboard.temp') }}")
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error(`HTTP error! status: ${response.status}`);
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        tempStatusTextElement.innerHTML = `${data.value}°C`;

                                        tempInfoContainer.classList.remove('bg-success', 'bg-danger', 'bg-secondary', 'bg-warning');
                                        // if (data.statusBgClass) {
                                        //     tempInfoContainer.classList.add(data.statusBgClass);
                                        // }

                                        // tempInfoContainer.classList.remove('bg-success', 'bg-danger', 'bg-secondary', 'bg-warning');

                                        if (data.temp >= 40) {
                                            tempInfoContainer.classList.add('bg-danger');
                                        } else if (data.temp<=40 && data.temp>=35) {
                                            tempInfoContainer.classList.add('bg-warning');
                                        } else {
                                            tempInfoContainer.classList.add('bg-success');  
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Fetch error for MSEB info:', error);
                                        tempStatusTextElement.textContent = 'Error';
                                        tempInfoContainer.className = 'temp-container bg-warning'; // Indicate error state
                                    });
                            }

                            // Initial call to display status immediately upon page load
                            updateTempInfoDisplay();
                            // Set interval for periodic updates (every 15 seconds)
                            // Store the interval ID on the window object to allow potential clearing if needed
                            window.tempUpdateInterval = setInterval(updateTempInfoDisplay, 200000);
                        </script>
                </li>
                <li class="nav-item">
                        <div class="mseb-container {{ $bgClass ?? '' }}" id="mseb-container">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-white-50">MSEB &nbsp;</span>
                      <h6 class="text-white mb-0" id="mseb-text">{{ $mseb ?? 'N/A' }}</h6>&nbsp;
                      <span class="text-white-50 mb-0 fw-bold" id="mseb-uptime">Since {{ $updowntime ?? '...' }}</span>
                    </div>
                  </div>
                  <script>
                            // This script is placed directly after the mseb-container element
                            // so the elements are guaranteed to be available when the script runs.
                            const msebInfoContainer = document.getElementById('mseb-container');
                            const msebStatusTextElement = document.getElementById('mseb-text');
                            const msebDurationTextElement = document.getElementById('mseb-uptime');

                            function updateMsebInfoDisplay() {
                                // Check if elements exist before attempting to update them.
                                // This is a safeguard, though they should always be present in the header.
                                if (!msebInfoContainer || !msebStatusTextElement || !msebDurationTextElement) {
                                    console.error('MSEB info elements not found in header. Clearing update interval.');
                                    // Clear the interval if elements are unexpectedly missing
                                    clearInterval(window.msebUpdateInterval);
                                    return;
                                }

                                fetch("{{ route('admin.dashboard.mseb') }}")
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error(`HTTP error! status: ${response.status}`);
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        msebStatusTextElement.innerHTML = data.msebStatus;
                                        msebDurationTextElement.innerHTML = "<strong>Since " + (data.msebDuration || "") + "</strong>";

                                        msebInfoContainer.classList.remove('bg-success', 'bg-danger', 'bg-secondary', 'bg-warning');
                                        if (data.statusBgClass) {
                                            msebInfoContainer.classList.add(data.statusBgClass);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Fetch error for MSEB info:', error);
                                        msebStatusTextElement.textContent = 'Error';
                                        msebDurationTextElement.textContent = 'Load failed';
                                        msebInfoContainer.className = 'mseb-container bg-warning'; // Indicate error state
                                    });
                            }

                            // Initial call to display status immediately upon page load
                            updateMsebInfoDisplay();
                            // Set interval for periodic updates (every 15 seconds)
                            // Store the interval ID on the window object to allow potential clearing if needed
                            window.msebUpdateInterval = setInterval(updateMsebInfoDisplay, 15000);
                        </script>
                  
                    </li>
              </ul>

              <!-- /mseb container -->
                
              <ul class="navbar-nav flex-row align-items-center">
                <!-- Place this tag where you want the button to render. -->

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="{{ asset('assets/img/avatar.png')}}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="{{ asset('assets/img/avatar.png')}}" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->roles->first()?->name ?? 'User' }}</small>
                            {{-- <small class="text-muted">Super Admin</small> --}}
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                          <span class="flex-grow-1 align-middle">Billing</span>
                          <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item align-middle" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                            this.closest('form').submit();">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">{{ __('Log Out') }}</span>
                            </a>
                        </form>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->