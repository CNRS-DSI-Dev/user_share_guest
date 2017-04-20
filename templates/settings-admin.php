<div id="usershareguest" class="section">
    <h2><?php p($l->t('Share to a guest')); ?></h2>
    <form id="usershareguest-form" method="POST">
        <p>
	        <label for="usershareguestinputday"><?php p($l->t('Number of days before the deletion of a guest account when it no longer connected sharing : '));?></label>
	        <input type="text" name="usershareguest-days" id="usershareguestinputday" value="<?php print_unescaped($_['usershareguest-days']) ?>" />
	    </p>
	    <p>
	        <label for="usershareguestinputstat"><?php p($l->t('Statistics sending date : '));?></label>
	        <input type="text" name="usershareguest-stats" id="usershareguestinputstat" value="<?php print_unescaped($_['usershareguest-stats']) ?>" placeholder="<?php print_unescaped($l->t('dd/mm')); ?>" />
        </p>
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']);?>" />
        <input type="submit" value="<?php print_unescaped($l->t('Save')); ?>"/>
        <span class='securitywarning'><?php print_unescaped($_['usershareguest-error-stats']); ?></span>
    </form>
    <form id="usershareguest-form-domains">
        <p>
            <label for="usershareguestdomain"><?php p($l->t('Specific domain allowed : '));?></label>
            <input type="text" name="usershareguest-domain" id="usershareguestdomain" placeholder="<?php print_unescaped($l->t('allowed.domain.net')); ?>" />
            <input type="submit" value="<?php print_unescaped($l->t('add')); ?>"/>
            <span class='securitywarning'></span>

        </p>
            <ul>
                <?php
                    if($_['usershareguest-domains'] != '') {
                        $domains = unserialize($_['usershareguest-domains']);
                        foreach($domains as $k => $v) {
                            ?>
                                <li><?php print_unescaped($v); ?><span class="guestDelete ui-icon" data-domain="<?php echo print_unescaped($v); ?>"> x </span></li>
                            <?php
                        }
                    }
                ?>
            </ul>
    </form>
    <div class="admin-guest-action">
        <button class="guest_launcher" data-link="<?php echo $_['usershareguest-link-stat']?>"><?php print_unescaped($l->t('Generate and send statistics')); ?></button>
        <?php
            if ($_['usershareguest-last-stat'] > 0) :
        ?>
                <p>
                    <?php 
                        p($l->t('Last launch : '));
                        print_unescaped(date('j/m/Y', $_['usershareguest-last-stat']));
                    ?>
                </p>
        <?php
            endif;
        ?>
    </div>
    <div class="admin-guest-action">
        <button class="guest_launcher" data-link="<?php echo $_['usershareguest-link-verif']?>"><?php print_unescaped($l->t('Launch account verification')); ?></button>
        <?php
            if ($_['usershareguest-last-verif'] > 0) :
        ?>
                <p>
                    <?php 
                        p($l->t('Last launch : '));
                        print_unescaped(date('j/m/Y', $_['usershareguest-last-verif']));
                    ?>
                </p>
        <?php
            endif;
        ?>
    </div>
    <div class="admin-guest-action">
        <button class="guest_launcher" data-link="<?php echo $_['usershareguest-link-clean']?>"><?php print_unescaped($l->t('Launch account cleaning')); ?></button></p>
        <?php
            if ($_['usershareguest-last-clean'] > 0) :
        ?>
                <p>
                    <?php 
                        p($l->t('Last launch : '));
                        print_unescaped(date('j/m/Y', $_['usershareguest-last-clean']));
                    ?>
                </p>
        <?php
            endif;
        ?>
    </div>
</div>

<?php  
//var_dump($_);exit(); 
?>
