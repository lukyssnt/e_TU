<!-- Notification System Include -->
<!-- Add this before </head> in your pages -->

<!-- Notification System JS -->
<script src="/e-TU/assets/js/notifications.js"></script>

<?php if (isset($flashMessage) && $flashMessage): ?>
    <script>
        // Display flash message after page load
        document.addEventListener('DOMContentLoaded', function () {
            showNotification(<?= json_encode($flashMessage) ?>, <?= json_encode($flashType ?? 'success') ?>);
        });
    </script>
<?php endif; ?>