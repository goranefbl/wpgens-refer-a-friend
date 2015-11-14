<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/admin
 * @author     Your Name <email@example.com>
 */
class WPGens_Settings_RAF extends WC_Settings_Page {

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
	 * @var      string    $gens_raf       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct() {

		$this->id    = 'gens_raf';
		$this->label = __( 'Refer A Friend', 'woocommerce');

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''         => __( 'General', 'woocommerce' ),
			'emails' => __( 'Email', 'woocommerce' ),
			'plugins' => __( 'More Free Plugins', 'woocommerce' )
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @param string $current_section Optional. Defaults to empty string.
	 * @return array Array of settings
	 */
	public function get_settings( $current_section = '' ) {
		$prefix = 'gens_raf_';
		switch ($current_section) {
			case 'emails':
				$settings = array(
					array(
						'name' => __( 'Email Settings', 'woocommerce' ),
						'type' => 'title',
						'desc' => 'Setup the look of email that will be sent to the referal together with coupon.',
						'id'   => 'email_options',
					),
					array(
						'id'			=> $prefix.'email_subject',
						'name' 			=> __( 'Email Subject', 'woocommerce' ),
						'type' 			=> 'text',
						'desc_tip'		=> __( 'Enter the subject of email that will be sent when notifiying the user of their coupon code.', 'woocommerce'),
						'default' 		=> 'Hey there!'
					),
					array(
						'id'			=> $prefix.'email_message',
						'name' 			=> __( 'Email Message', 'woocommerce' ),
						'type' 			=> 'textarea',
						'class'         => 'input-text wide-input',
						'desc'			=> __( 'Text that will appear in email that is sent to user once they get the code. Use {{code}} to add coupon code.HTML allowed.', 'woocommerce'),
						'default' 		=> 'You referred someone! Here is your coupone code reward: {{code}} .'
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'woocommerce' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'email_options',
					),
				);
				break;

			case 'plugins':
				$settings = array(
					array(
						'name' => __( 'Check out all of our super cool plugins', 'woocommerce' ),
						'type' => 'title',
						'desc' => sprintf( __( 'Thanks for using Refer a Friend plugin. If you have any cool idea that we could add to plugin, be sure to contact us at <a target="_blank" href="%s">goranefbl@gmail.com</a>. 
						<br/>Our plugins are coded with best practices in mind, they will not slow down your site or spam database. Guaranteed to work and always up to date.
						Check out all of our plugins at: <a target="_blank" href="%s">this link.</a>', 'woocommerce' ), 'mailto:goranefbl@gmail.com', 'https://profiles.wordpress.org/goran87/#content-plugins'),
						'id'   => 'plugin_options',
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'Plugins', 'woocommerce' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'plugin_options',
					),
				);
				break;

			default:
				$settings = array(
					array(
						'name' => __( 'General', 'woocommerce' ),
						'type' => 'title',
						'desc' => 'General Options, setup plugin here first.',
						'id'   => 'general_options',
					),
					array(
						'id'			=> $prefix.'disable',
						'name' 			=> __( 'Disable', 'woocommerce' ),
						'type' 			=> 'checkbox',
						'label' 		=> __( 'Disable Coupons', 'woocommerce' ), // checkbox only
						'desc'			=> __( 'Check to disable. Referal links wont work anymore.', 'woocommerce'),
						'default' 		=> 'no'
					),
					array(
						'id'		=> $prefix.'cookie_time',
						'name' 		=> __( 'Cookie Time', 'woocommerce' ),
						'type' 		=> 'number',
						'desc_tip'	=> __( 'As long as cookie is saved, user will recieve coupon after referal purchase product.', 'woocommerce'),
						'desc' 		=> 'How long to keep cookies before it expires.(In days)'
					),
					array(
						'id'		=> $prefix.'cookie_remove',
						'name' 		=> __( 'Single Purchase', 'woocommerce' ),
						'label' 		=> __( 'Single Purchase', 'woocommerce' ), // checkbox only
						'type' 			=> 'checkbox',
						'desc_tip'	=> __( 'This means that coupon is sent only the first time referral makes a purchase, as referral cookie is deleted after it.', 'woocommerce'),
						'desc' 		=> 'If checked, cookie will be deleted after customer makes a purchase.'
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'woocommerce' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'general_options',
					),
					array(
						'name' => __( 'Coupon Settings', 'woocommerce' ),
						'type' => 'title',
						'desc' => 'General Options, setup plugin here first.',
						'id'   => 'coupon_options',
					),
					array(
						'id'			=> $prefix.'coupon_type',
						'name' 			=> __( 'Coupon Type', 'woocommerce' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 			=> 'select',
						'class'    => 'wc-enhanced-select',
						'options'		=> array(
							'fixed_cart'	=> 'Cart Discount',
							'percent'	=> 'Cart % Discount',
//							'fixed_product'	=> 'Product Discount',
//							'percent_product'	=> 'Product % Discount'
						)
					),
					array(
						'id'		=> $prefix.'coupon_amount',
						'name' 		=> __( 'Coupon Amount', 'woocommerce' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'number',
						'desc_tip'	=> __( ' Entered without the currency unit or a percent sign as these will be added automatically, e.g., ’10’ for 10£ or 10%.', 'woocommerce'),
						'desc' 		=> 'Fixed value or percentage off depending on the discount type you choose.'
					),
					/*
					array(
						'id'		=> $prefix.'coupon_duration',
						'name' 		=> __( 'Coupon Duration', 'woocommerce' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'text',
						'class'		=> 'date-picker hasDatepicker',
						'desc' 		=> 'Value is number of days beginning on the coupon creation date.'
					),
					*/
					array(
						'id'		=> $prefix.'min_order',
						'name' 		=> __( 'Minimum Order', 'woocommerce' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'number',
						'desc' 		=> 'Define minimum order subtotal in order for coupon to work.'
					),
					array(
						'id'		=> $prefix.'individual_use',
						'name' 		=> __( 'Individual Use', 'woocommerce' ),
						'type' 		=> 'checkbox',
						'desc' 	=> __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'woocommerce' ), // checkbox only
						'default' 	=> 'no'
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'woocommerce' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'coupon_options',
					),
				);
				break;
		}

		/**
		 * Filter Memberships Settings
		 *
		 * @since 1.0.0
		 * @param array $settings Array of the plugin settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

	}

	/**
	 * Output the settings
	 *
	 * @since 1.0
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}


	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}

}

return new WPGens_Settings_RAF();
