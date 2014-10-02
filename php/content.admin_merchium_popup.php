<div id="<?php echo $code; ?>" title="<?php echo $title; ?>" class="merchium-dialog hidden">
    <div class="merchium-dialog-content">
        <form class="merchium-form" id="<?php echo $code; ?>_form" method="post">
            <?php if (!empty($login)) : ?>
                <input type="hidden" name="login" value="Y">
            <?php endif; ?>
            
            <div class="error-box error-message"></div>
            <div class="info-box info-message"></div>

            <div class="merchium-inputs">

                <?php if (empty($login)) : ?>
                    <div class="merchium-form-field">
                        <label> <?php _e('Your store name', 'merchium'); ?> <span class="mandatory">*</span> </label>
                        <div class="error-field-box hidden">
                            <span><?php _e('Field is mandatory', 'merchium'); ?></span>
                            <div class="arrow-bottom-error"></div>
                        </div>
                        <input class="required" type="text" name="domain" value="<?php echo $store_name; ?>">
                    </div>
                <?php endif; ?>

                <div class="merchium-form-field">
                    <label> <?php _e('Email address', 'merchium'); ?> <span class="mandatory">*</span> </label>
                    <div class="error-field-box hidden">
                        <span><?php _e('Field is mandatory', 'merchium'); ?></span>
                        <div class="arrow-bottom-error"></div>
                    </div>
                    <input class="required email" type="text" name="email" value="<?php echo $email; ?>">
                </div>

                <div class="merchium-form-field">
                    <label> <?php _e('Password', 'merchium'); ?> <span class="mandatory">*</span> </label>
                    <div class="error-field-box hidden">
                        <span><?php _e('Field is mandatory', 'merchium'); ?></span>
                        <div class="arrow-bottom-error"></div>
                    </div>
                    <input class="required" type="password" name="password">
                </div>

                <div class="<?php if (empty($login)) : ?>hidden <?php endif; ?>merchium-recover-password">
                    <a class="merchium-recover-password-link" href="#"><?php _e('Recover password', 'merchium'); ?></a>
                </div>

                <input type="submit" value="<?php echo $submit; ?>" />

                <div class="store-registration-loader"></div>
                <div class="clear"></div>

            </div>

        </form>
    </div>
</div>
