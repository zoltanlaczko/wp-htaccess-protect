<?php

// Add the settings menu link to the Settings menu in admin interface
function zotya_hp_add_settings_menu(){
	global $ZOTYA_HP;
	// Create a new link to the settings menu
	// Returns the suffix of the page that can later be used in the actions
	$page = add_options_page(
		'htaccess protect',
		'htaccess protect',
		'manage_options',
		'zotya-htaccess-protect',
		'zotya_hp_display_settings_page'
	);

	// If a form was submitted
	if ( isset( $_POST['zotya_hp_add_user']) ) add_action( "admin_head-$page", array( $ZOTYA_HP, 'add_user' ) );
	elseif ( isset( $_POST['zotya_hp_user_remove'] ) ) add_action( "admin_head-$page", array( $ZOTYA_HP, 'remove_user' ) );
	elseif ( isset( $_POST['zotya_hp_user_modify'] ) ) add_action( "admin_head-$page", array( $ZOTYA_HP, 'modify_user' ) );
	elseif ( isset( $_POST['zotya_hp_settings'] ) ) add_action( "admin_head-$page", array( $ZOTYA_HP, 'change_settings' ) );
}
add_action( 'admin_menu', 'zotya_hp_add_settings_menu' );

// Display the settings page in the admin interface
function zotya_hp_display_settings_page(){
	global $ZOTYA_HP;
	?>
	<div id="zotya_hp" class="wrap">
		<h2>htaccess protect</h2>

		<?php if ( !$ZOTYA_HP->plugin_has_sufficient_permissions() ): ?>

			<div class="form-wrapper warning not-working">
				<h4><?php esc_html_e('The following files need to be writable by WordPress for this plugin to work:', 'zotya-htaccess-protect'); ?></h4>
				<p><?php echo $ZOTYA_HP->htaccess_root; ?> - <?php $_SESSION['htaccess_root'] ? esc_html_e('writable', 'zotya-htaccess-protect') : esc_html_e('not writable', 'zotya-htaccess-protect'); ?></p>
				<p><?php echo $ZOTYA_HP->htpasswd_file; ?> - <?php $_SESSION['htpasswd'] ? esc_html_e('writable', 'zotya-htaccess-protect') : esc_html_e('not writable', 'zotya-htaccess-protect'); ?></p>
				<p><?php echo $ZOTYA_HP->htaccess_admin; ?> - <?php $_SESSION['htaccess_admin'] ? esc_html_e('writable', 'zotya-htaccess-protect') : esc_html_e('not writable', 'zotya-htaccess-protect'); ?></p>
			</div>

		<?php endif; ?>

		<?php if ( isset( $_SESSION['zotya_hp_msg'] ) ): ?>
			<div class="updated"><p><strong><?php echo $_SESSION['zotya_hp_msg']; unset( $_SESSION['zotya_hp_msg'] ); ?></strong></p></div>
		<?php endif; ?>

		<?php $users = $ZOTYA_HP->get_htpasswd_users(); $user_count = count( $users ); ?>

		<div class="form-wrapper">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<h3><?php esc_html_e('Enable/disable protection', 'zotya-htaccess-protect'); ?></h3>
				<?php wp_nonce_field( 'zotya_hp_settings' ); ?>
				<?php $options = unserialize( get_option( 'zotya_hp_options' ) ); ?>

				<?php if ( ! $user_count ): ?>
					<p class="warning"><?php esc_html_e( 'You have to have at least one user to enable the protection', 'zotya-htaccess-protect' ); ?></p>
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th><?php esc_html_e('Protection for wp-admin', 'zotya-htaccess-protect' ); ?></th>
						<td><label>
							<input type="radio" class="enable" name="zotya_hp_admin" value="1" <?php echo $options['zotya_hp_admin'] ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?>/><?php esc_html_e('Enabled', 'zotya-htaccess-protect' ); ?>
						</label><br/>
						<label>
							<input type="radio" class="disable" name="zotya_hp_admin" value="0" <?php echo !$options['zotya_hp_admin'] ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?> /><?php esc_html_e('Disabled', 'zotya-htaccess-protect' ); ?>
						</label>
						<p class="subnote"><?php esc_html_e('By enabling this setting, you will probably be asked for the password as soon as you save this option (or go to any other admin page) - so be careful.', 'zotya-htaccess-protect' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e('Protection for wp-login.php', 'zotya-htaccess-protect' ); ?></th>
					<td><label>
						<input type="radio" class="enable" name="zotya_hp_login" value="1" <?php echo $options['zotya_hp_login'] ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?>/><?php esc_html_e('Enabled', 'zotya-htaccess-protect' ); ?>
					</label><br/>
					<label>
						<input type="radio" class="disable" name="zotya_hp_login" value="0" <?php echo $options['zotya_hp_login'] == 0 ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?> /><?php esc_html_e('Disabled', 'zotya-htaccess-protect' ); ?>
					</label>
					<p class="subnote"><?php esc_html_e('By enabling this setting, you will be asked for this password before you can access the login page again. This is in addition to and separate from the WordPress login.', 'zotya-htaccess-protect' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Protection for the whole site', 'zotya-htaccess-protect' ); ?></th>
				<td><label>
					<input type="radio" class="enable" name="zotya_hp_site" value="1" <?php echo $options['zotya_hp_site'] ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?>/><?php esc_html_e('Enabled', 'zotya-htaccess-protect' ); ?>
				</label><br/>
				<label>
					<input type="radio" class="disable" name="zotya_hp_site" value="0" <?php echo $options['zotya_hp_site'] == 0 ? 'checked="checked"' : ''; echo ! $user_count ? ' disabled="disabled"' : ''; ?> /><?php esc_html_e('Disabled', 'zotya-htaccess-protect' ); ?>
				</label>
				<p class="subnote"><?php esc_html_e('By enabling this setting, you will be asked for this password before you can access ANY PAGE again. Only the people with username/password will be able to see any page of the site - be careful!', 'zotya-htaccess-protect' ); ?></p>
			</td>
		</tr>
	</table>
	<p>
		<input class="submit" type="submit" value="<?php esc_html_e( 'Save', 'zotya-htaccess-protect' ); ?>" name="zotya_hp_settings" <?php echo ! $user_count ? ' disabled="disabled"' : ''; ?>>
	</p>
</form>

</div><!-- end .form-wrapper -->
<div class="form-wrapper" id="users-modify">
	<h3><?php esc_html_e('Modify users', 'zotya-htaccess-protect' ); ?></h3>
	<table class="form-table">
		<thead>
			<th><?php esc_html_e('Username', 'zotya-htaccess-protect' ); ?></th>
			<th><?php esc_html_e('Modify password', 'zotya-htaccess-protect' ); ?></th>
			<th><?php esc_html_e('Remove user', 'zotya-htaccess-protect' ); ?></th>
		</thead>

		<?php foreach( $users as $user ): ?>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<?php wp_nonce_field( "zotya_hp_user_$user" ); ?>
				<tr>
					<td>
						<strong><?php echo $user; ?></strong>
					</td>
					<td>
						<input type="password" name="pwd_user" placeholder="<?php esc_html_e('New password', 'zotya-htaccess-protect' ); ?>" />
						<input class="submit" type="submit" value="<?php esc_html_e('Change password', 'zotya-htaccess-protect' ); ?>" name="zotya_hp_user_modify" />
					</td>
					<td>
						<input type="hidden" name="username" value="<?php echo $user; ?>" />
						<input class="remove" type="submit" value="<?php esc_html_e('Remove User', 'zotya-htaccess-protect' ); ?>" name="zotya_hp_user_remove" />
					</td>
				</tr>
			</form>
		<?php endforeach; ?>
	</table>
</div>
<div class="form-wrapper">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<h3><?php esc_html_e('Add new user', 'zotya-htaccess-protect' ); ?></h3>
		<?php wp_nonce_field( 'zotya_hp_add_user' ); ?>
		<table class="form-table">
			<tr>
				<th>
					<?php esc_html_e( 'Username', 'zotya-htaccess-protect' ); ?>
				</th>
				<td><input name="new_username" type="text" /></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Password', 'zotya-htaccess-protect' ); ?></th>
				<td>
					<input name="new_password" type="password" />
				</td>
			</tr>
		</table>
		<p>
			<input class="submit" type="submit" value="<?php esc_html_e( 'Add user', 'zotya-htaccess-protect' ); ?>" name="zotya_hp_add_user">
		</p>
	</form>
</div>
</div>
<?php
}

// Add links
function zotya_hp_set_plugin_meta( $links, $file ) {
	if ( strpos( $file, 'zotya-htaccess-protect.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="' . get_admin_url() . 'options-general.php?page=zotya-htaccess-protect">' . esc_html__( 'Settings', 'zotya-htaccess-protect' ) . '</a>' ) );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'zotya_hp_set_plugin_meta', 10, 2 );
