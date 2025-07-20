<nav class="bg-white border-bottom shadow-sm">
    <div class="container-fluid py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">

        <a class="fw-bold text-primary text-decoration-none me-3 flex-shrink-0" href="{{ route('landing') }}" style="white-space: nowrap; font-size: clamp(0.85rem, 1.2vw, 1.2rem);">
            LOGO APP
        </a>

        @auth
            @if(Auth::user()->role === 'user')
                <form method="GET" action="{{ url()->current() }}" class="flex-grow-1" style="max-width: 300px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari event..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm text-white" style="background-color: #0367A6;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            @elseif(Auth::user()->role === 'admin')
                <div class="text-primary fw-bold text-center flex-grow-1 small text-nowrap" style="font-size: clamp(0.8rem, 1.2vw, 1rem);">
                    Kompetisi Pariwisata Indonesia
                </div>
            @endif
        @endauth

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end flex-shrink-0">

            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="border-color: #0367A6; color: #0367A6;">
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm" style="background-color: #0367A6; border-color: #0367A6;">
                    Sign Up
                </a>
            @endguest

            @auth
                @if(Auth::user()->role === 'user')
                    <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm" style="border-color: #0367A6;">
                        Home
                    </a>
                    <a href="{{ route('events.list') }}" class="btn btn-primary btn-sm" style="background-color: #0367A6; border-color: #0367A6;">
                        My Event
                    </a>
                @elseif(Auth::user()->role === 'admin')
                    <a href="{{ route('dashboard.index') }}" class="btn btn-outline-primary btn-sm" style="border-color: #0367A6;">
                        Home
                    </a>
                @endif

                <a href="{{ route('profile.show') }}" class="d-inline-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                        alt="Profile" class="rounded-circle" width="28" height="28">
                </a>
            @endauth

        </div>
    </div>
</nav>
