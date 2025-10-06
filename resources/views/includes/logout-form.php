<!-- Logout Form (Hidden) - Required for POST logout -->
<form id="logout-form" action="<?php echo route('logout'); ?>" method="POST" style="display: none;">
    <?php echo csrf_field(); ?>
</form>
