@if (session('success'))
    <script>
        $(document).ready(function() {
            toastr.options = {
                "timeOut": "2000",
            };
            toastr.success('{{ session('success') }}');
        });
    </script>
@endif

@if (session('error'))
    <script>
        $(document).ready(function() {
            toastr.options = {
                "timeOut": "2000",
            };
            toastr.error('{{ session('error') }}');
        });
    </script>
@endif
