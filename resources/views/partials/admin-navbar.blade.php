@php $prefix = auth('admin')->user()?->routePrefix() ?? 'employee'; @endphp
<nav class="navbar navbar-expand-lg navbar-dark navbar-admin">
    <div class="container-fluid">
        <a class="navbar-brand" href="/{{ $prefix }}">
            <img src="/images/logo.png" alt="APO Box" height="32">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            @auth('admin')
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/{{ $prefix }}/dashboard"><i data-lucide="layout-dashboard" class="icon--sm"></i> Dashboard</a></li>
                    @if(auth('admin')->user()->role === 'manager')
                        <li class="nav-item"><a class="nav-link" href="/{{ $prefix }}/reports/index"><i data-lucide="bar-chart-3" class="icon--sm"></i> Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="/{{ $prefix }}/logs/view"><i data-lucide="file-text" class="icon--sm"></i> Logs</a></li>
                        <li class="nav-item"><a class="nav-link" href="/{{ $prefix }}/affiliate-links"><i data-lucide="link" class="icon--sm"></i> Affiliate Links</a></li>
                        <li class="nav-item"><a class="nav-link" href="/{{ $prefix }}/tools"><i data-lucide="wrench" class="icon--sm"></i> Tools</a></li>
                    @endif
                </ul>
            @endauth
            <ul class="navbar-nav ms-auto">
                @auth('admin')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i data-lucide="settings" class="icon"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end settings-dropdown p-3" style="min-width: 280px;">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Printer IP Address</label>
                                <input id="Settings.local.printer_ip" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Scale ID</label>
                                @foreach(['apo1' => 'APO 1', 'apo2' => 'APO 2', 'apo3' => 'APO 3', 'legacy' => 'Legacy'] as $val => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="scale-id" id="scale-id-{{ $val }}" value="{{ $val }}" @if($val === 'apo1') checked @endif>
                                        <label class="form-check-label small" for="scale-id-{{ $val }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mb-0">
                                <label class="form-label small fw-semibold">Scale Status</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="scale-status" id="scale-status-on" value="On" checked>
                                        <label class="form-check-label small" for="scale-status-on">On</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="scale-status" id="scale-status-off" value="Off">
                                        <label class="form-check-label small" for="scale-status-off">Off</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="/admin/logout"><i data-lucide="log-out" class="icon--sm"></i> Logout</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="/admin/login">Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
