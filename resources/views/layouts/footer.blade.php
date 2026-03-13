<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 text-center">
                <script>document.write(new Date().getFullYear())</script> © 
                @if (env('APP_ENV') === 'production')
                    {{ env('APP_NAME') }}
                @else
                    {{ env('APP_NAME') }}
                @endif
                .
            </div>
        </div>
    </div>
</footer>
