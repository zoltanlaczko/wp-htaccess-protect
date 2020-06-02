<?php
/**
* Main class for htaccess protect
* @package zotya_htaccess_protect
*/

class ZOTYA_HP {
	public $htpasswd_file;
	public $htaccess_root;
	public $htaccess_admin;

	function __construct() {
		$this->htpasswd_file = ABSPATH . '.htpasswd';
		$this->htaccess_root = ABSPATH . '.htaccess';
		$this->htaccess_admin = ABSPATH . 'wp-admin/.htaccess';
	}

	/**
	* Adds new user to the .htpasswd file
	* @return boolean
	*/
	function add_user() {
		// Check the referer
		check_admin_referer( 'zotya_hp_add_user' );
		// If the fields are set
		if ( isset( $_POST['new_username'] ) ) {
			// sanitize the text field
			$username = sanitize_user( $_POST['new_username'] );
		}
		// If the password field is set
		if ( isset( $_POST['new_password'] ) ) {
			$password = sanitize_text_field( $_POST['new_password'] );
		}
		// if both fields exists
		if ( !empty( $username ) && !empty( $password ) ) {
			// If user can manage options
			if ( current_user_can( 'manage_options' ) ) {
				$this->modify_htpasswd( $username, $password, 'add' );
				$_SESSION['zotya_hp_msg'] = esc_html__( 'New user added', 'zotya-htaccess-protect' );
				return true;
			}
		}
		return false;
		$_SESSION['zotya_hp_msg'] = esc_html__( 'Something went wrong. Please try again.', 'zotya-htaccess-protect' );
	}

	/**
	* Removes user from .htpasswd file
	* @return boolean
	*/
	function remove_user() {
		// If current user can manage options
		if ( current_user_can( 'manage_options' ) ) {
			// If the username to remove is set
			if ( isset( $_POST['username'] ) ){
				$username = sanitize_user( $_POST['username'] );
			}
			if ( !empty( $username ) ){
				// Check the admin referer
				check_admin_referer( "zotya_hp_user_$username" );
				// Remove the user
				$this->modify_htpasswd( $username, '', 'delete' );
				$_SESSION['zotya_hp_msg'] = esc_html__( 'User removed.', 'zotya-htaccess-protect' );

				// If there are no users left after deleting
				if ( ! count( $this->get_htpasswd_users() ) ) {
					// Disable the plugin by removing the htaccess rows not to get locked out
					$this->remove_wp_admin_protection();
					$this->remove_wp_root_protection();
					// Modify the saved options
					$options = array( 'zotya_hp_admin'=>0, 'zotya_hp_login'=>0, 'zotya_hp_site'=>0, 'zotya_hp_fix_auth'=>0 );
					update_option( 'zotya_hp_options', serialize( $options ) );
					$_SESSION['zotya_hp_msg'] = esc_html__( 'User removed. The locks were disabled: there are no users left.', 'zotya-htaccess-protect' );
				}
				return true;
			}
		}
		return false;
		$_SESSION['zotya_hp_msg'] = esc_html__( 'Something went wrong. Please try again.', 'zotya-htaccess-protect' );
	}

	/**
	* Change user password in the htpasswd file
	* @return boolean
	*/
	function modify_user() {
		// If user has admin permissions
		if ( current_user_can( 'manage_options' ) ) {
			// If username is set
			if ( isset( $_POST['username'] ) ) {
				$username = sanitize_user( $_POST['username'] );
			}
			// If the password is set
			if ( isset( $_POST['pwd_user'] ) ) {
				$password = sanitize_text_field( $_POST['pwd_user'] );
			}
			if ( !empty( $username ) && !empty( $password ) ) {
				// Check the referer
				check_admin_referer( "zotya_hp_user_$username" );
				// Modify the file
				$this->modify_htpasswd( $username, $password, 'modify' );
				$_SESSION['zotya_hp_msg'] = esc_html__( 'User password changed.', 'zotya-htaccess-protect' );
				return true;
			}
		}
		$_SESSION['zotya_hp_msg'] = esc_html__( 'Something went wrong. Please try again.', 'zotya-htaccess-protect' );
	}

	/**
	* Modifies the .htpasswd file
	*
	* @param String $username
	* @param String $pass
	* @param String $action
	* @return boolean
	*/
	function modify_htpasswd( $username, $pass, $action ) {
		if ( !file_exists( $this->htpasswd_file ) || is_writeable( $this->htpasswd_file ) ) {
			// Encrypt the password
			$password = base64_encode( sha1( $pass, true ) );
			$content = '';
			// If file doesn't exist
			if ( ! file_exists( $this->htpasswd_file ) ) {
				// Take the content of the file as empty
				$lines = '';
			}
			// Otherwise get the content
			else {
				$lines = explode( "\n", implode( '', file( $this->htpasswd_file ) ) );
			}

			if ( ! $f = @fopen( $this->htpasswd_file, 'w' ) ) return false;

			// If any lines exist in the file
			if ( $lines ){
				$found = false;
				// render the lines and compare the the username
				foreach ( $lines as $line ) {
					$line = preg_replace( '/\s+/', '', $line ); // remove spaces
					if ( $line ) {
						if ( strpos( $line, ':{SHA}') !== false ) {
							list( $user, $pass ) = explode( ":{SHA}", $line, 2 );
						} else {
							$user = $pass = false;
						}
						// If it's for removing the user
						if ( $action == 'delete' ) {
							// If the line isn't for the user being removed, add the line to the file
							if ( $user != $username && ( $user && $pass ) ) {
								$content .= $user . ':{SHA}' . $pass . "\n";
							}
						}
						// In other cases
						else {
							// If user found
							if ( $user == $username ) {
								// Add new password
								$content .= $username . ':{SHA}' . $password . "\n";
								// Mark the user found
								$found = true;
							} else {
								if ( $user && $pass ) {
									$content .= $user . ':{SHA}' . $pass . "\n";
								}
							}
						}
					}
				}
			}
			// If it's not removing the user
			if ( $action != 'delete' ) {
				// If there is no content, or the user to modify wasn't find, add the user to the end
				if ( ! strlen( trim( $content ) ) || !$found ) {
					$content .= $username . ':{SHA}' . $password;
				}
			}
			$content = explode( "\n", $content );
			// Write the content to the file
			foreach ( $content as $contentline ) {
				fwrite( $f, $contentline . "\n" );
			}
			fclose( $f );
		}
	}

	/**
	* Removes the rows from .htaccess that password protect wp-login.php
	*/
	function remove_wp_root_protection() {
		$this->zotya_hp_remove_with_markers( $this->htaccess_root, 'ZOTYA htaccess protect' );
	}

	/**
	* Remove the added rows from .htaccess file for locking up wp-admin
	*/
	function remove_wp_admin_protection() {
		$this->zotya_hp_remove_with_markers( $this->htaccess_admin, 'ZOTYA htaccess protect' );
	}

	/**
	* Gets the list of the users entered to .htpasswd
	*/
	function get_htpasswd_users() {
		//Create an empty array for users
		$users = array();
		//If the file doesn't exist or isn't readable, return
		if ( ! file_exists( $this->htpasswd_file ) || ! is_readable( $this->htpasswd_file ) ) {
			return $users;
		}
		// If can't open the file, return
		if ( !$f = @fopen( $this->htpasswd_file, 'r' ) ) {
			return $users;
		}
		// Create an array of the lines in file
		$lines = explode( "\n", implode( '', file( $this->htpasswd_file ) ) );
		// If no lines, return
		if ( !$lines ) {
			return $users;
		}
		// Loop the lines
		foreach ($lines as $line) {
			// If line exists after trim
			if ( trim( $line ) ) {
				// Split the line by ':{SHA}'
				if ( strpos( $line, ':{SHA}') !== false ) {
					list( $user, $pass ) = explode( ":{SHA}", $line, 2 );
					// Add the found user to users' array
					$users[] = $user;
				}
			}
		}
		fclose( $f );
		// Return the found users
		return $users;
	}

	/**
	* Register plugin scripts
	*/
	function register_plugin_scripts(){
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'zotya-hp-script', plugin_dir_url( ZOTYA_HP_FILE ) . 'js/script.js', array( 'jquery' ) );
		wp_enqueue_style( 'zotya-hp-style', plugin_dir_url( ZOTYA_HP_FILE ) . 'css/style.css' );

	}

	/**
	* Enable or disable the wp-admin and/or wp-login.php password protection
	*/
	function change_settings() {
		//C heck the form referer for security reasons
		check_admin_referer( "zotya_hp_settings" );
		// Only allow is the user can manage options
		if ( current_user_can( 'manage_options' ) ) {
			//Get the existing options
			$options = unserialize( get_option( 'zotya_hp_options' ) );
			//Modify and update the options
			$options['zotya_hp_admin'] = (int) rest_sanitize_boolean( $_POST['zotya_hp_admin'] );
			$options['zotya_hp_login'] = (int) rest_sanitize_boolean( $_POST['zotya_hp_login'] );
			$options['zotya_hp_site'] = (int) rest_sanitize_boolean( $_POST['zotya_hp_site'] );
			$options['zotya_hp_fix_auth'] = (int) rest_sanitize_boolean( $_POST['zotya_hp_fix_auth'] );
			update_option( 'zotya_hp_options', serialize( $options ) );

			// If the wp-admin lock was enabled
			if ( $options['zotya_hp_admin'] ) $this->add_wp_admin_protection($options['zotya_hp_fix_auth']);
			else $this->remove_wp_admin_protection();

			$this->remove_wp_root_protection();

			// If all the site protection was enabled
			if ( $options['zotya_hp_site'] ) $this->add_wp_root_protection( 'all', $options['zotya_hp_fix_auth'] );

			// If the wp-login.php lock was enabled
			elseif ( $options['zotya_hp_login'] ) $this->add_wp_root_protection( 'login', $options['zotya_hp_fix_auth'] );

			$_SESSION['zotya_hp_msg'] = esc_html__( 'Settings saved.', 'zotya-htaccess-protect' );
		}
	}

	/**
	* Add wp-admin password protection
	*/
	function add_wp_admin_protection( $fix_auth = false ){
		if ( ! function_exists( 'insert_with_markers' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		}
		$insertion = '';
		if ( $fix_auth ) $insertion .= "\tSetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0\n";
		$insertion .= "\tAuthUserFile " . $this->htpasswd_file . "\n\tAuthType basic\n\tAuthName \"Restricted\"\n\trequire valid-user\n\tErrorDocument 401 \"Authorization Required\"\n\t# Stop Apache from serving .ht* files\n\t<Files ~ \"^\.ht\">\n\tOrder allow,deny\n\tDeny from all\n\t</Files>\n\t<Files admin-ajax.php>\n\tOrder allow,deny\n\tAllow from all\n\tSatisfy any\n\t</Files>\n\t<Files ~ \"\.(css|js|svg|png|jpeg|jpg|gif)$\">\n\tOrder allow,deny\n\tAllow from all\n\tSatisfy any\n\t</Files>";

		// Since it has to be an array, explode
		$insertion = explode( "\n", $insertion );
		insert_with_markers( $this->htaccess_admin, 'ZOTYA htaccess protect', $insertion );
	}

	/**
	* Add root password protection
	*/
	function add_wp_root_protection( $type, $fix_auth = false ){
		if ( ! function_exists( 'insert_with_markers' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		}
		$insertion = "\t# Stop Apache from serving .ht* files\n\t<Files ~ \"^\.ht\">\n\tOrder allow,deny\n\tDeny from all\n\t</Files>\n";
		if ( $type == 'login' ) $insertion .= "\t# Protect wp-login\n\t<Files wp-login.php>\n";
		if ( $fix_auth ) $insertion .= "\tSetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0\n";
		$insertion .= "\tAuthUserFile " . $this->htpasswd_file . "\n\tAuthType basic\n\tAuthName \"Restricted\"\n\trequire valid-user\n\tErrorDocument 401 \"Authorization Required\"\n";
		if ( $type == 'login' ) $insertion .= "\t</Files>\n";
		$insertion .= "\t<Files admin-ajax.php>\n\torder allow,deny\n\tallow from all\n\t</Files>";

		//Since it has to be an array, explode
		$insertion = explode( "\n", $insertion );
		insert_with_markers( $this->htaccess_root, 'ZOTYA htaccess protect', $insertion );
	}

	/**
	* Check if the plugin has sufficient permissions
	* @return boolean
	*/
	function plugin_has_sufficient_permissions() {
		$_SESSION['htaccess_admin'] = 1;
		$_SESSION['htaccess_root'] = 1;
		$_SESSION['htpasswd'] = 1;
		// Check if .htaccess for admin folder is writeable
		$has_permissions = true;
		// If file exists but is not writeable, return false
		if ( file_exists( $this->htaccess_admin ) && !is_writeable( $this->htaccess_admin  ) ) {
			$_SESSION['htaccess_admin'] = 0;
			$has_permissions = false;
		}
		// If file doesn't exist and the folder isn't writeable, return false
		elseif ( !file_exists( $this->htaccess_admin  ) && !is_writeable( ABSPATH.'wp-admin/'  ) ) {
			$_SESSION['htaccess_admin'] = 0;
			$has_permissions = false;
		}
		// Check if .htpasswd file is writeable
		// If file doesn't exist and the folder isn't writeable
		if ( !file_exists( $this->htpasswd_file ) && !is_writeable( ABSPATH ) ) {
			$_SESSION['htpasswd'] = 0;
			$has_permissions = false;
		}
		elseif ( file_exists( $this->htpasswd_file ) && !is_writeable( $this->htpasswd_file ) ) {
			$_SESSION['htpasswd'] = 0;
			$has_permissions = false;
		}
		// Check if .htaccess file for wp-login.php is writeable
		// If file doesn't exist and the folder isn't writeable
		if ( !file_exists( $this->htaccess_root ) && !is_writeable( ABSPATH ) ) {
			$_SESSION['htaccess_root'] = 0;
			$has_permissions = false;
		}
		elseif ( file_exists( $this->htaccess_root ) && !is_writeable( $this->htaccess_root ) ) {
			$_SESSION['htaccess_root'] = 0;
			$has_permissions = false;
		}
		return $has_permissions;
	}

	/**
	* Remove lines from .htaccess files with markers
	* @param string $filename
	* @param string $marker
	* @return boolean
	*/
	function zotya_hp_remove_with_markers( $filename, $marker ) {
		if ( !file_exists( $filename ) || is_writeable( $filename ) ) {
			$marker_regex = '/# BEGIN ' . $marker . '+?# END ' . $marker . '/s';
			$content = file_get_contents( $filename );
			$new_content = preg_replace( $marker_regex, '', $content );
			file_put_contents( $filename, $new_content );
			return true;
		} else {
			return false;
		}
	}

	/**
	* On plugin activation
	*/
	function activate() {
		// Create the default options
		$options = array( 'zotya_hp_admin'=>0, 'zotya_hp_login'=>0, 'zotya_hp_site'=>0, 'zotya_hp_fix_auth'=>0 );
		update_option( 'zotya_hp_options', serialize( $options ), true );
	}

	/**
	* On plugin deactivation
	*/
	function deactivate() {
		// Remove rows for locking up
		$this->remove_wp_root_protection();
		$this->remove_wp_admin_protection();

		// Set options to default
		$options = array( 'zotya_hp_admin'=>0, 'zotya_hp_login'=>0, 'zotya_hp_site'=>0, 'zotya_hp_fix_auth'=>0 );
		update_option( 'zotya_hp_options', serialize( $options ) );
	}
}
