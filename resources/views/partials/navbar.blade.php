<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">APO Box</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth('customer')
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/account') }}">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/orders') }}">My Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/requests/add') }}">Custom Package Request</a></li>
                </ul>
            @endauth
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="https://apobox.zendesk.com/hc/en-us" target="_blank">Customer Support</a></li>
                @auth('customer')
                    <li class="nav-item"><a class="nav-link" href="{{ url('/logout') }}">Logout</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/login') }}">Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
