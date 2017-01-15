<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/public
 * @author     Your Name <email@example.com>
 */
class Gens_RAF_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gens_raf    The ID of this plugin.
	 */
	private $gens_raf;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $gens_raf       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $gens_raf, $version ) {

		$this->gens_raf = $gens_raf;
		$this->version = $version;

	}

	/**
	 * Save RAF(User) ID in Order Meta after Order is Complete
	 * woocommerce_checkout_update_order_meta hook
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	public function save_raf_id( $order_id ) {
		$active = get_option( 'gens_raf_disable' );
		/*
		$order = new WC_Order( $order_id );
	    $myuser_id = (int)$order->user_id;
	    $user_info = get_userdata($myuser_id);
	    $items = $order->get_items();
		*/
		if ( isset($_COOKIE["gens_raf"]) && $active != "yes" ) {
			$rafID = $_COOKIE["gens_raf"];
			update_post_meta( $order_id, '_raf_id', esc_attr($rafID));
		}
    	return $order_id;
	}

	/**
	 * Generate coupon and email it after order status has been changed to complete
	 * woocommerce_order_status_completed hook
	 *
	 * @since    1.0.0
	 */
	public function gens_create_send_coupon($order_id) {
		$rafID = esc_attr(get_post_meta( $order_id, '_raf_id', true));
		$order = wc_get_order( $order_id );
		$order_total = $order->get_total();
		$minimum_amount = get_option( 'gens_raf_min_ref_order' );

		$gens_users = get_users( array(
			"meta_key" => "gens_referral_id",
			"meta_value" => $rafID,
			"number" => 1, 
			"fields" => "ID"
		) );
		$user_id = $gens_users[0];

		if ( $gens_users && !empty($rafID) ) { // if array is not empty and id exists

			if($minimum_amount && $minimum_amount > $order_total) {
				return $order_id; //exit, dont generate
			}
			// Generate Coupon and returns it
			$coupon_code = $this->generate_coupons( $user_id  ); 
			// Send via Email
			$this->gens_send_email( $user_id, $coupon_code );
		}
		return $order_id;
	}

	/**
	 * Send Email to user
	 *
	 * @since    1.0.0
	 */
	public function gens_send_email($user_id,$coupon_code) {

		if ( !$user_id || !$coupon_code) {
			return false;
		}

		global $woocommerce;
		$mailer = $woocommerce->mailer();

		$user_info = get_userdata($user_id);
		$user_email = $user_info->user_email;
		$user_message = get_option( 'gens_raf_email_message' );
		$subject = get_option( 'gens_raf_email_subject' );
		ob_start();
		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) );
		echo str_replace( '{{code}}', $coupon_code, $user_message );
		wc_get_template( 'emails/email-footer.php' );
		$message = ob_get_clean();
		// Debug wp_die($user_email);
		$mailer->send( $user_email, $subject, $message);

	}

	/**
	 * Show or call to generate new referal ID
	 *
	 * @since    1.0.0
	 * @return string
	 */
	public function get_referral_id($user_id) {

		if ( !$user_id ) {
			return false;
		}
		$referralID = get_user_meta($user_id, "gens_referral_id", true);
		if($referralID && $referralID != "") {
			return $referralID;
		} else {
			do{
			    $referralID = $this->generate_referral_id();
			} while ($this->exists_ref_id($referralID));
			update_user_meta( $user_id, 'gens_referral_id', $referralID );
			return $referralID;
		}

	}

	/**
	 * Check if ID already exists
	 *
	 * @since    1.0.0
	 * @return string
	 */
	public function exists_ref_id($referralID) {

		$args = array('meta_key' => "gens_referral_id", 'meta_value' => $referralID );
		if (get_users($args)) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Generate a new Referral ID
	 *
	 * @since    1.0.0
	 * @return string
	 */
	function generate_referral_id($randomString="ref")
	{

	    $characters = "0123456789";
	    for ($i = 0; $i < 7; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}


	/**
	 * Generate a coupon for userID
	 *
	 * @since    1.0.0
	 * @return string
	 */
	public function generate_coupons( $user_id ) {
		$user_info = get_userdata($user_id);
		$user_email = $user_info->user_email;
		$coupon_code = substr( "abcdefghijklmnopqrstuvwxyz123456789", mt_rand(0, 50) , 1) .substr( md5( time() ), 1); // Code
		$amount = get_option( 'gens_raf_coupon_amount' );
		$duration = get_option( 'gens_raf_coupon_duration' );
		$individual = get_option( 'gens_raf_individual_use' );
		$discount_type = get_option( 'gens_raf_coupon_type' );
		$minimum_amount = get_option( 'gens_raf_min_order' );		
		$coupon = array(
			'post_title' => $coupon_code,
			'post_excerpt' => 'Referral coupon for: '.$user_email,
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type'		=> 'shop_coupon'
		);
							
		$new_coupon_id = wp_insert_post( $coupon );

		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
		update_post_meta( $new_coupon_id, 'individual_use', $individual );
		update_post_meta( $new_coupon_id, 'product_ids', '' );
		update_post_meta( $new_coupon_id, 'customer_email', $user_email );
		update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
		update_post_meta( $new_coupon_id, 'usage_limit', '1' ); // Only one coupon
		update_post_meta( $new_coupon_id, 'expiry_date', '' );
		update_post_meta( $new_coupon_id, 'minimum_amount', $minimum_amount );
		update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
		update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

		if($new_coupon_id) {
			return $coupon_code;			
		} else {
			return "Error creating coupon";
		}

	}

	/**
	 * Remove Cookie after checkout if Setting is set
	 * woocommerce_thankyou hook
	 *
	 * @since    1.0.0
	 */
	public function remove_cookie_after( $order_id ) {
		$remove = get_option( 'gens_raf_cookie_remove' );
		if (isset($_COOKIE['gens_raf']) && $remove == "yes") {
		    unset($_COOKIE['gens_raf']);
		    setcookie('gens_raf', '', time() - 3600, '/'); // empty value and old timestamp
		}
	}

	/**
	 * Show Unique URL - get referral id and create link
	 * woocommerce_before_my_account hook
	 *
	 * @since    1.0.0
	 */
	public function account_page_show_link() {

		$referral_id = $this->get_referral_id( get_current_user_id() );
		$refLink = esc_url(add_query_arg( 'raf', $referral_id, get_home_url() )); 
	?>
		<div class="woocommerce-message"><?php _e( 'Your Referal URL:','woocommerce'); ?> <a href="<?php echo $refLink; ?>" ><?php echo $refLink; ?></a></div>
	<?php
	}

	/**
	 * Account page - list unused referral coupons
	 * woocommerce_before_my_account hook
	 *
	 * @since    1.0.0
	 */
	public function account_page_show_coupons() {
		$user_info = get_userdata(get_current_user_id());
		$user_email = $user_info->user_email;
		$date_format = get_option( 'date_format' );
		$args = array(
		    'posts_per_page'   => -1,
		    'post_type'        => 'shop_coupon',
		    'post_status'      => 'publish',
			'meta_query' => array (
			    array (
				  'key' => 'customer_email',
				  'value' => $user_email,
	              'compare' => 'LIKE'
			    )
			),
		);
		    
		$coupons = get_posts( $args );

		if($coupons) { ?>

			<h2><?php echo apply_filters( 'wpgens_raf_title', __( 'Unused Refer a Friend Coupons', 'woocommerce' ) ); ?></h2>
			<table class="shop_table shop_table_responsive">
				<tr>
					<th><?php _e('Coupon code','woocommerce'); ?></th>
					<th><?php _e('Coupon discount','woocommerce'); ?></th>
				</tr>
		<?php
			foreach ( $coupons as $coupon ) {
				$discount = get_post_meta($coupon->ID, "coupon_amount" ,true);
				$discount_type = get_post_meta($coupon->ID, "discount_type" ,true);
				$usage_count = get_post_meta($coupon->ID, "usage_count" ,true);
				$expiry_date = get_post_meta($coupon->ID,"expiry_date",true);
				
				if($discount_type == "percent_product" || $discount_type == "percent") {
					$discount = $discount."%";
				}
				
				if($usage_count == 0) { // If coupon isnt used yet.
					echo '<tr>';
					echo '<td>'.$coupon->post_title.'</td>';
					echo '<td>'.$discount.'</td>';
					echo '</tr>';
				} 

			}
			echo '</table';
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gens_raf.'_cookieJS', plugin_dir_url( __FILE__ ) . 'js/cookie.min.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->gens_raf, plugin_dir_url( __FILE__ ) . 'js/gens-raf-public.js', array( 'jquery' ), $this->version, false );
		$time = get_option( 'gens_raf_cookie_time' );
		$cookies = array( 'timee' => $time );
		wp_localize_script( $this->gens_raf, 'gens_raf', $cookies );
	}

}
