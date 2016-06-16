<div id="usershareguest" class="section">
    <h2><?php p($l->t('Share to a guest')); ?></h2>
    <form id="usershareguest-form" method="POST">
        <label for="usershareguestinput"><?php p($l->t('Number of days before the deletion of a guest account when it no longer connected sharing : '));?></label>
        <input type="text" name="usershareguest-days" id="usershareguestinput" value="<?php print_unescaped($_['usershareguest-days']) ?>" />
        <input type="submit" value="<?php print_unescaped($l->t('save')); ?>"/>
        <span class='securitywarning'><?php print_unescaped($_['usershareguest-error']); ?></span>
    </form>
</div>
