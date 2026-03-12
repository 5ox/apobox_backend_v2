<nav class="navbar navbar-expand-lg navbar-dark navbar-apobox">
    <div class="container">
        <a class="navbar-brand" href="/">
            <i data-lucide="package" class="icon--lg"></i>
            <span>APO Box</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth('customer')
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/account') }}"><i data-lucide="layout-dashboard" class="icon--sm"></i> My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/orders') }}"><i data-lucide="package" class="icon--sm"></i> My Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/requests/add') }}"><i data-lucide="plus-circle" class="icon--sm"></i> Custom Package Request</a></li>
                </ul>
            @endauth
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="https://apobox.zendesk.com/hc/en-us" target="_blank"><i data-lucide="life-buoy" class="icon--sm"></i> Support</a></li>
                @auth('customer')
                    <li class="nav-item"><a class="nav-link" href="{{ url('/logout') }}"><i data-lucide="log-out" class="icon--sm"></i> Logout</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/login') }}">Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
