<footer class="app-footer py-2 px-3 d-flex justify-content-between align-items-center border-top">

    <div class="footer-left">
        <strong>
            &copy; 2014–<span id="currentYear"></span>
            <a
                href="https://sazzadul-islam-website.vercel.app/"
                class="text-decoration-none">
                Sazzadul Islam Portfolio
            </a>
        </strong>
        <span class="ms-1">All rights reserved.</span>
    </div>

    <div class="footer-right text-muted d-none d-sm-inline">
        Created by <strong>Sazzadul Islam Molla</strong>
    </div>

</footer>

<script>
    document.getElementById("currentYear").textContent = new Date().getFullYear();
</script>