<nav class="bg-white border-bottom shadow-sm">
    <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between py-2 px-3 gap-2">

        <a class="fw-bold text-primary text-decoration-none" href="#" style="white-space: nowrap;">LOGO APP</a>

        @auth
            @if(Auth::user()->role === 'user')
                <form method="GET" action="{{ url()->current() }}" class="flex-grow-1" style="max-width: 350px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control border" placeholder="Cari event..." value="{{ request('search') }}">
                        <button type="submit" class="input-group-text" style="background-color: #0367A6; color: white;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            @elseif(Auth::user()->role === 'admin')
                <div class="text-primary fw-bold text-center flex-grow-1">
                    Kompetisi Pariwisata Indonesia
                </div>
            @endif
        @endauth

        <div class="d-flex align-items-center gap-2 flex-wrap">

            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="border-color: #0367A6; color: #0367A6;">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm" style="background-color: #0367A6; border-color: #0367A6;">Sign Up</a>
            @endguest

            @auth
                @if(Auth::user()->role === 'user')
                    <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm" style="border-color: #0367A6;">Home</a>
                    <a href="{{ route('events.list') }}" class="btn btn-primary btn-sm" style="background-color: #0367A6; border-color: #0367A6;">My Event</a>
                @endif

                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('dashboard.index') }}" class="btn btn-outline-primary btn-sm" style="border-color: #0367A6;">Home</a>
                @endif

                <a href="{{ route('profile.show') }}" class="d-inline-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                        alt="Profile" class="rounded-circle" width="30" height="30">
                </a>
            @endauth

        </div>
    </div>
</nav>
