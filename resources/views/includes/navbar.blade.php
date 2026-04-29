<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached premium-navbar"
     id="layout-navbar">

<style>
.premium-navbar {
    background: #ffffff;
    border-radius: 18px;
    padding: 14px 22px;
    box-shadow: 0 12px 32px rgba(249, 115, 22, 0.12);
}

.navbar-flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

/* tanggal */
.nav-date{
    color: #f97316;
    font-weight: 600;
    font-size: 15px;
}

/* watermark */
.nav-watermark{
    text-align: right;
    font-size: 12px;
    color: #f97316;
    line-height: 1.3;
}
.nav-watermark a{
    color: #f97316;
    text-decoration: none;
}
.nav-watermark a:hover {
    color: #ea580c;
    text-decoration: underline;
}

/* alert styling */
.alert-success {
    background: #d1fae5 !important;
    color: #065f46 !important;
    border: none !important;
}
.alert-danger {
    background: #fee2e2 !important;
    color: #991b1b !important;
    border: none !important;
}
</style>

<div class="layout-menu-toggle d-xl-none me-2">
    <a href="javascript:void(0)">
        <i class="bx bx-menu bx-sm"></i>
    </a>
</div>

<div class="navbar-flex">

    <!-- kiri tanggal -->
    <div id="nav-date" class="nav-date">---</div>

    <!-- tengah kosong -->
    <div>
        @if(session('success'))
        <div id="flash-success"
            class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-2 shadow"
            role="alert"
            style="min-width:260px; z-index:1050; border-radius:8px; opacity:1; transition:opacity .6s;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>

        <script>
            setTimeout(function() {
                const alertBox = document.getElementById('flash-success');
                if (alertBox) {
                    alertBox.style.opacity = "0";  
                    setTimeout(() => alertBox.remove(), 600); 
                }
            }, 2500); 
        </script>
        @endif

        @if(session('error'))
        <div id="flash-error"
            class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-2 shadow"
            role="alert"
            style="min-width:260px; z-index:1050; border-radius:8px; opacity:1; transition:opacity .6s;">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        </div>

        <script>
            setTimeout(function() {
                const alertBox = document.getElementById('flash-error');
                if (alertBox) {
                    alertBox.style.opacity = "0";   
                    setTimeout(() => alertBox.remove(), 600); 
                }
            }, 2500); 
        </script>
        @endif

        @if(session('warning'))
        <div id="flash-warning"
            class="alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3 px-4 py-2 shadow"
            role="alert"
            style="min-width:260px; z-index:1050; border-radius:8px; opacity:1; transition:opacity .6s; background: #fffbeb; color: #92400e; border: none;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
        </div>

        <script>
            setTimeout(function() {
                const alertBox = document.getElementById('flash-warning');
                if (alertBox) {
                    alertBox.style.opacity = "0";   
                    setTimeout(() => alertBox.remove(), 600); 
                }
            }, 4000); 
        </script>
        @endif
    </div>

    <!-- kanan watermark -->
    <div class="nav-watermark">
        Created by <b>Magang Kemnaker</b><br>
        <a href="https://instagram.com/fzlns21" target="_blank">@fzlns21</a> |
        <a href="https://instagram.com/bolehservice" target="_blank">@dhiyaind</a>
    </div>

</div>
</nav>

<script>
function updateDateTime() {
    const now = new Date();

    const tanggal = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    document.getElementById('nav-date').innerText = tanggal;
}
updateDateTime();
</script>