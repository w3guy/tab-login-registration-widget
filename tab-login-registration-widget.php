<?php
/*
Plugin Name: Tabbed Login Registration Widget
Plugin URI: http://sitepoint.com
Description: A tabbed login and registration widget for WordPress
Version: 1.0
Author: Agbonghama Collins
Author URI: http://w3guy.com
License: GPL2
*/

/**
 * Adds Tab_Login_Registration widget.
 */

// Turn on output buffering
ob_start();


class Tab_Login_Registration extends WP_Widget {

	static private $login_registration_status;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'tab_login_registration', // Base ID
			__( 'Tabbed Login Registration Widget', 'text_domain' ), // Name
			array( 'description' => __( 'A tabbed login and registration widget for WordPress', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Returns the HTML for the login form
	 * @return string
	 */
	static function login_form() {
		$html = '<form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';
		$html .= '<input type="text" name="login_username" placeholder="Username" /><br/>';
		$html .= '<input type="password" name="login_password" placeholder="Password" /><br/>';
		$html .= '<input type="checkbox" name="remember_login" value="true" checked="checked"/> Remember Me<br/>';
		$html .= '<input type="submit" name="login_submit" value="Login" /><br/>';
		$html .= '</form>';

		return $html;

	}


	/**
	 * Returns the HTML code for the registration form
	 * @return string
	 */
	static function registration_form() {
		$html = '<form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';
		$html .= '<input type="text" name="registration_username" placeholder="Username" /><br/>';
		$html .= '<input type="password" name="registration_password" placeholder="Password" /><br/>';
		$html .= '<input type="email" name="registration_email" placeholder="Email" /><br/>';
		$html .= '<input type="submit" name="reg_submit" value="Sign Up" /><br/>';
		$html .= '</form>';

		return $html;
	}


	/**
	 * Register new users
	 */
	function register_user() {

		if ( isset( $_POST['reg_submit'] ) ) {

			$username = esc_attr( $_POST['registration_username'] );
			$password = esc_attr( $_POST['registration_password'] );
			$email    = esc_attr( $_POST['registration_email'] );

			$register_user = wp_create_user( $username, $password, $email );

			if ( $register_user && ! is_wp_error( $register_user ) ) {

				self::$login_registration_status = 'Registration completed.';
			} elseif ( is_wp_error( $register_user ) ) {
				self::$login_registration_status = $register_user->get_error_message();
			}

		}
	}


	/**
	 * Login registered users
	 */
	function login_user() {
		if ( isset( $_POST['login_submit'] ) ) {

			$creds                  = array();
			$creds['user_login']    = esc_attr( $_POST['login_username'] );
			$creds['user_password'] = esc_attr( $_POST['login_password'] );
			$creds['remember']      = esc_attr( $_POST['remember_login'] );

			$login_user = wp_signon( $creds, false );

			if ( ! is_wp_error( $login_user ) ) {
				wp_redirect( home_url( 'wp-admin' ) );
			} elseif ( is_wp_error( $login_user ) ) {
				self::$login_registration_status = $login_user->get_error_message();
			}
		}
	}


	public function widget( $args, $instance ) { ?>
		<script type="text/javascript">
			$('document').ready(function () {
				$('#flip-container').quickFlip();

				$('#flip-navigation li a').each(function () {
					$(this).click(function () {
						$('#flip-navigation li').each(function () {
							$(this).removeClass('selected');
						});
						$(this).parent().addClass('selected');
						var flipid = $(this).attr('id').substr(4);
						$('#flip-container').quickFlipper({}, flipid, 1);

						return false;
					});
				});
			});
		</script>

		<?php
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>

		<?php $this->login_user(); ?>

		<?php $this->register_user(); ?>

		<div class="login-reg-error"><?php echo self::$login_registration_status; ?></div>
		<div id="flip-tabs">
			<ul id="flip-navigation">
				<li class="selected"><a href="#" id="tab-0">Login</a></li>
				<li><a href="#" id="tab-1">Register</a></li>
			</ul>
			<div id="flip-container">
				<div>
					<ul class="orange">
						<?php echo self::login_form(); ?>
					</ul>
				</div>
				<div>
					<ul class="green">
						<?php echo self::registration_form(); ?>
					</ul>
				</div>
			</div>
		</div>

		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Login / Registration', 'text_domain' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Tab_Login_Registration

// register Foo_Widget widget
function register_tab_login_registration() {
	register_widget( 'Tab_Login_Registration' );
}

add_action( 'widgets_init', 'register_tab_login_registration' );


function plugin_assets() {
	wp_enqueue_style( 'tlrw-styles', plugins_url( 'css/styles.css', __FILE__ ) );
	wp_enqueue_script( 'tlrw-jquery', plugins_url( 'js/jquery.js', __FILE__ ) );
	wp_enqueue_script( 'tlrw-quickflip', plugins_url( 'js/jquery.quickflip.js', __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'plugin_assets' );