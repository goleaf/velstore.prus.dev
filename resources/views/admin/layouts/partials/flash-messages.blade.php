@if (session('success'))
    <script>
        toastr.success(@json(session('success')), @json(__('cms.notifications.success')));
    </script>
@endif

@if (session('error'))
    <script>
        toastr.error(@json(session('error')), @json(__('cms.notifications.error')));
    </script>
@endif

@if (session('warning'))
    <script>
        toastr.warning(@json(session('warning')), @json(__('cms.notifications.warning')));
    </script>
@endif

@if (session('info'))
    <script>
        toastr.info(@json(session('info')), @json(__('cms.notifications.info')));
    </script>
@endif

@if ($errors->any())
    <script>
        (function(messages) {
            if (!Array.isArray(messages) || !messages.length) {
                return;
            }
            messages.forEach(function(message) {
                toastr.error(message, @json(__('cms.notifications.validation_error')));
            });
        })(@json($errors->all()));
    </script>
@endif
