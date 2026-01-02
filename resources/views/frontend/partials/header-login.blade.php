<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <!-- Brand -->
           <a class="navbar-brand" href="{{ route('customer.dashboard') }}" 
                style="font-size: 28px; font-weight: 700; background: linear-gradient(45deg, #6a11cb, #2575fc); 
                        -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Kragncy
                </a>


            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-lg-center">
                     <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.rules') }}">Rules</a>
                    </li>
                    <li class="nav-item ml-3">
                        <a href="@php echo route('login'); @endphp" class="nav-link">
                            <i class="fas fa-user"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
