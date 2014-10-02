<?php // Popups ?>

<?php // Register ?>
<?php $login = false; ?>
<?php $code = 'merchium_register'; ?>
<?php $title = __('Sign up', 'merchium'); ?>
<?php $submit = __('Finish & Create Store', 'merchium'); ?>
<?php $store_name = $_store_name; ?>
<?php $email = $_email; ?>
<?php include (MERCHIUM_PLUGIN_DIR . 'php/content.admin_merchium_popup.php'); ?>

<?php // Login ?>
<?php $login = true; ?>
<?php $code = 'merchium_login'; ?>
<?php $title = __('Login to your store', 'merchium'); ?>
<?php $submit = __('Login', 'merchium'); ?>
<?php $store_name = ''; ?>
<?php $email = ''; ?>
<?php include (MERCHIUM_PLUGIN_DIR . 'php/content.admin_merchium_popup.php'); ?>

<?php // \Popups ?>

<div class="wrap">

    <form method="POST" action="options.php" class="merchium-settings">
        <h2><?php _e('Merchium Store - General settings', 'merchium'); ?></h2>
        <?php settings_fields('merchium_options_page'); ?>
        <div class="merchium-message-image">
            <div class="merchium-image-container">
                <?php if (!$is_connected) : ?>
                    <img class="merchium-image" src="<?php echo plugins_url('images/merchium-store.png', MERCHIUM_PLUGIN_FILE); ?>" width="120" />
                <?php else : ?>
                    <img class="merchium-image store-connected" src="<?php echo plugins_url('images/merchium-store.png', MERCHIUM_PLUGIN_FILE); ?>" width="120" />
                <?php endif; ?>
            </div>
            <div class="merchium-message">
                <?php if (!$is_connected) : ?>
                    <h3><?php _e('Thank you for choosing Merchium!', 'merchium'); ?></h3>
                    <div>
                        <?php _e('Add a Merchium online shop in your WordPress site in 3 easy steps.', 'merchium'); ?>
                    </div>
                <?php else : ?>
                    <h3><?php _e('Congratulations!', 'merchium'); ?></h3>
                    <div>
                        <?php _e('Your Merchium store is now connected to your WordPress website.', 'merchium'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <hr />

        <?php if (!$is_connected) : ?>
            <ol>
        <?php else : ?>
            <ul>
        <?php endif; ?>

            <?php if (!$is_connected) : ?>
                <li>
                    <h4><?php _e('Sign up at Merchium', 'merchium'); ?></h4>
                    <div>
                        <?php _e('Create a new Merchium account which you will use to manage your store and inventory. The registration is free.', 'merchium'); ?>
                    </div>
                    <div class="merchium-account-buttons">
                        <a class="merchium-dialog-opener" rev="merchium_register">
                            <?php _e('Create a free account', 'merchium'); ?>
                        </a>
                        <a class="merchium-dialog-opener" rev="merchium_login">
                            <?php _e('I already have a Merchium account, log in', 'merchium'); ?>
                        </a>
                    </div>
                </li>
                <li>
                    <h4><?php _e('Copy the store widget code', 'merchium'); ?></h4>
                    <div>
                        <?php _e('Open your Merchium admin panel, go to <strong>Design → Layouts</strong>, and copy the <strong>Widget code</strong>. You can pick a layout you want to use for your WordPress shop.', 'merchium'); ?>
                    </div>
                </li>
            <?php endif; ?>
            
            <?php if ($is_connected) : ?>
                <div class="merchium-account-buttons store-connected">
                    <a class="merchium-dialog-opener" rev="merchium_login">
                        <?php _e('Control panel', 'merchium'); ?>
                    </a>
                    <a target="_blank" href="<?php echo esc_attr(get_page_link(get_option('merchium_store_page_id'))); ?>">
                        <?php _e('Visit storefront', 'merchium'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <li class="merchium_widget_code_enter">
                <?php if (!$is_connected) : ?>
                    <h4>
                        <?php _e('Paste the code', 'merchium'); ?>
                    </h4>
                    <div><label for="merchium_widget_code"><?php _e('Paste the code in the box below and click Connect store:', 'merchium'); ?></label></div>
                <?php else : ?>
                    <h4>
                        <?php _e('Store widget code', 'merchium'); ?>
                    </h4>
                <?php endif; ?>
                <div class="merchium-widget-code-box">
                    <input type="hidden" name="merchium_widget_code" value="" />
                    <textarea id="merchium_widget_code" name="merchium_widget_code" cols="100" rows="11"<?php if ($is_connected) : ?> disabled<?php endif; ?>><?php echo get_option('merchium_widget_code'); ?></textarea>
                </div>
                <?php if (!$is_connected) : ?>
                    <div class="merchium-connect-store">
                        <button type="submit" class="button-primary"><?php _e('Connect store', 'merchium'); ?></button>
                    </div>
                <?php else : ?>
                    <div class="TODO">
                        <?php
                            echo str_replace(
                                '[meta]',
                                'href="#" onclick="jQuery(\'form.merchium-settings\').submit(); return false;"',
                                __('To connect another store, <a [meta]>disconnect the current one</a> and enter the new store widget code.', 'merchium')
                            );
                        ?>
                    </div>
                <?php endif; ?>

            </li>
        <?php if (!$is_connected) : ?>
            </ol>
        <?php else : ?>
            </ul>
        <?php endif; ?>
        <hr />
        <p><?php _e('Feel free to <a href="http://help.merchium.com">contact us</a> if you have any questions about Merchium.', 'merchium'); ?></p>
    </form>
</div>
