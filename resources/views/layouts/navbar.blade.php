<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid py-2 px-4">
        <a class="navbar-brand fw-bold text-primary" href="#">LOGO APP</a>

        @auth
            @if(Auth::user()->role === 'user')
                <form method="GET" action="{{ url()->current() }}" class="d-flex" style="max-width: 600px; width: 100%;">
                    <div class="input-group w-100">
                        <input type="text" name="search" class="form-control border" placeholder="Cari event..." value="{{ request('search') }}">
                        <button type="submit" class="input-group-text" style="background-color: #0367A6; color: white;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            @endif
            @if(Auth::user()->role === 'admin')
                <div class="d-flex flex-grow-1 justify-content-center"">
                    <a class="navbar-brand fw-bold text-primary" href="#">
                        Kompetisi Pariwisata Indonesia
                    </a>
                </div>
            @endif
        @endauth

        <div class="d-flex">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2" style="border-color: #0367A6; color: #0367A6;">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary" style="background-color: #0367A6; border-color: #0367A6;">Sign Up</a>
            @endguest

            @auth
                @if(Auth::user()->role === 'user')
                    <a href="{{ route('landing') }}" class="btn btn-outline-primary me-2" style="border-color: #0367A6;">Home</a>
                    <a href="{{ route('events.list') }}" class="btn btn-primary me-3" style="background-color: #0367A6; border-color: #0367A6;">My Event</a>
                @endif
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary me-2" style="border-color: #0367A6;">Home</a>
                @endif
                <a href="{{ route('profile.show') }}" class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff" 
                        alt="Profile" class="rounded-circle" width="35" height="35">
                </a>
            @endauth
        </div>
    </div>
</nav>
