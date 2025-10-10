<?php
$content = file_get_contents('/home/fadhil/Documents/Code/billiardpro/resources/views/livewire/transactions/receipt-print.blade.php');
$newContent = $content . '
@endsection

@push(\'scripts\')
<script>
    // Update the theme icon based on current theme
    document.addEventListener(\'DOMContentLoaded\', function() {
        const theme = document.documentElement.getAttribute(\'data-theme\');
        const themeIcon = document.getElementById(\'theme-icon\');
        if (theme === \'dark\') {
            themeIcon.textContent = \'☀️\';
        } else {
            themeIcon.textContent = \'🌙\';
        }
    });
</script>
@endpush
';
file_put_contents('/home/fadhil/Documents/Code/billiardpro/resources/views/livewire/transactions/receipt-print.blade.php', $newContent);
?>