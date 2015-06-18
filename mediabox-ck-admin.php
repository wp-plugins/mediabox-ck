<?php
defined('ABSPATH') or die;

class MediaboxckAdmin {

	public $pluginname, $pluginurl, $plugindir, $options, $settings_field, $settings,  $ispro, $prourl, $pagehook, $options_yesno;

	public $default_settings = 
	array( 	
			'fxduration' => '300'
			);

	function __construct() {
		$this->pluginname = 'mediabox-ck';
		$this->pluginurl = plugins_url( '', __FILE__ );
		$this->plugindir = plugin_dir_path( __FILE__ );
		$this->settings_field = 'mediaboxck_options';
		$this->options = get_option( $this->settings_field );
		$this->prourl = 'http://www.wp-pluginsck.com/en/wordpress-plugins/mediabox-ck';
		// $this->ispro = file_exists($this->plugindir . '/includes/class-' . $this->pluginname . '-pro.php');

		// default option
		$this->options_yesno = array(
				'1' => __('Yes')
				, '0' => __('No')
				);
		
		add_action( 'admin_init', array($this, 'admin_init'), 20 );
		add_action( 'admin_menu', array($this, 'admin_settings_menu'), 20 );

		// add the settings and get pro link in the plugins list
		add_filter( 'plugin_action_links', array( $this, 'show_pro_message_action_links'), 10, 2 );
		
		add_action('plugins_loaded', array($this, 'mediaboxck_init') );
	}

	function mediaboxck_init() {
		load_plugin_textdomain( $this->pluginname, false, dirname( plugin_basename( __FILE__ ) ) . '/language/'  );
	}


	function admin_init() {
		register_setting( $this->settings_field, $this->settings_field);
	}

	function admin_settings_menu() {
		if ( ! current_user_can('update_plugins') )
			return;

		// add a new submenu to the standard Settings panel
		$this->pagehook = $page =  add_options_page(
		__('Mediabox CK', 'mediabox-ck'), __('Mediabox CK', 'mediabox-ck'), 
		'administrator', $this->pluginname, array($this,'render_options') );

		// executed on-load. Add all metaboxes and create the row in the options table
		add_action( 'load-' . $page, array( $this, 'add_metaboxes' ) );
		// load the assets for the plugin page only
		add_action("admin_head-$page", array($this, 'load_assets') );
	}
	
	function load_assets() {
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('postbox');
		wp_enqueue_script(array('jquery', 'jquery-ui-tooltip'));
		// wp_enqueue_script('mediaboxck_adminscript', $this->pluginurl . '/assets/mediaboxck_admin.js', array('jquery', 'jquery-ui-button', 'wp-color-picker'));
		?>
		<style type="text/css">
		#ckwrapper label { float: left; width: 200px; }
		#ckwrapper input { max-width: 100%; }
		#ckwrapper .ckheading { color: #2EA2CC; font-weight: bold; }
		#ckwrapper span { display: inline-block; }
		#ckwrapper .wp-color-result, #ckwrapper img, #ckwrapper fieldset { vertical-align: middle; }
		.settings-error { clear: both; }
		#ckwrapper .tabck { margin-top: 1.3em; }
		.noteck { background: #e5e5e5; color: #1a1a1a; padding: 7px; }
		.tooltipckdesc {
			position: relative;
			background: rgba(0,0,0,0.7);
			padding: 4px 7px;
			color: #f3f3f3;
			border-radius: 3px;
			float: left;
			font-size: 1em;
			max-width: 250px;
		}
		.tooltipckdesc:after {
			content: "";
			display: block;
			position: absolute;
			width: 0;
			height: 0;
			bottom: -8px;
			left: 10px;
			border-style: solid;
			border-width: 8px 4px 0 4px;
			border-color: rgba(0,0,0,0.7) transparent transparent transparent;
		}
		</style>
	<?php }
	
	function add_metaboxes() {
		// set the entry in the database options table if not exists
		add_option( $this->settings_field, $this->default_settings );
		// add the metaboxes
		add_meta_box( 'mediaboxck-general', __('General Options', 'mediabox-ck'), array( $this, 'create_metabox_general' ), $this->pagehook, 'general' );
		add_meta_box( 'mediaboxck-styles', __('Styles Options', 'mediabox-ck'), array( $this, 'create_metabox_styles' ), $this->pagehook, 'styles' );
		add_meta_box( 'mediaboxck-effects', __('Effects Options', 'mediabox-ck'), array( $this, 'create_metabox_effects' ), $this->pagehook, 'effects' );
		add_meta_box( 'mediaboxck-mobile', __('Mobile Options', 'mediabox-ck'), array( $this, 'create_metabox_mobile' ), $this->pagehook, 'mobile' );
	}
	
	function create_metabox_general() {
		?>
		<div class="ckheading"><?php _e('Link detection', 'mediabox-ck') ?></div>
		<div>
			<label for="<?php echo $this->get_field_id( 'attribtype' ); ?>" class="hasTooltip" title="<?php _e( 'Set if you want to apply to the links that have for example : class=&quot;lightbox&quot; or rel=&quot;lightbox&quot;', 'mediabox-ck') ?>"><?php _e( 'Attribute detection', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/link.png" />
			<?php $options_attribtype = array(
				'className' => __('Class', 'mediabox-ck')
				, 'rel' => __('Rel', 'mediabox-ck')
				);
			?>
			<?php echo $this->get_field('select', $this->get_field_name( 'attribtype' ), $this->get_field_value( 'attribtype', 'rel' ), '', $options_attribtype) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'attribname' ); ?>"><?php _e( 'Attribute name', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/link_edit.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'attribname' ), $this->get_field_value( 'attribname', 'lightbox' )) ?>
		</div>
		<div class="ckheading"><?php _e('Dimensions', 'mediabox-ck') ?></div>
		<div>
			<label for="<?php echo $this->get_field_id( 'defaultwidth' ); ?>"><?php _e( 'Default width', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/width.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'defaultwidth' ), $this->get_field_value( 'defaultwidth', '640')) ?>px
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'defaultheight' ); ?>"><?php _e( 'Default height', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/height.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'defaultheight' ), $this->get_field_value( 'defaultheight', '360')) ?>px
		</div>
		<div class="ckheading"><?php _e('Display', 'mediabox-ck') ?></div>
		<div>
			<label for="<?php echo $this->get_field_id( 'showcaption' ); ?>" class="hasTooltip" title="<?php _e( 'Show the title attribute of the link as caption in the lightbox', 'mediabox-ck') ?>"><?php _e( 'Show caption', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/text_signature.png" />
			<?php echo $this->get_field('radio', $this->get_field_name( 'showcaption' ), $this->get_field_value( 'showcaption', '1'), '', $this->options_yesno) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'showcounter' ); ?>" class="hasTooltip" title="<?php _e( 'Display the number of medias beside the caption', 'mediabox-ck') ?>"><?php _e( 'Show counter', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/book_open.png" />
			<?php echo $this->get_field('radio', $this->get_field_name( 'showcounter' ), $this->get_field_value( 'showcounter', '1'), '', $this->options_yesno) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'loop' ); ?>" class="hasTooltip" title="<?php _e( 'If you click the next button on the last media, it comes back to the first one. Else the next button is not available', 'mediabox-ck') ?>"><?php _e( 'Loop medias', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/control_repeat.png" />
			<?php echo $this->get_field('radio', $this->get_field_name( 'loop' ), $this->get_field_value( 'loop', '1'), '', $this->options_yesno) ?>
		</div>
		<?php
	}

	function create_metabox_styles() {
		?>
		<div class="ckheading"><?php _e('Appearance', 'mediabox-ck') ?></div>
		<div>
			<label for="<?php echo $this->get_field_id( 'cornerradius' ); ?>"><?php _e( 'Corner radius', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/border_radius_tl.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'cornerradius' ), $this->get_field_value( 'cornerradius', '10')) ?>px
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'shadowoffset' ); ?>"><?php _e( 'Shadow width', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/shadow_blur.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'shadowoffset' ), $this->get_field_value( 'shadowoffset', '5')) ?>px
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'bgcolor' ); ?>"><?php _e( 'Background Color', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
			<?php echo $this->get_field('color', $this->get_field_name( 'bgcolor' ), $this->get_field_value( 'bgcolor')) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'overlaycolor' ); ?>"><?php _e( 'Overlay Color', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
			<?php echo $this->get_field('color', $this->get_field_name( 'overlaycolor' ), $this->get_field_value( 'overlaycolor')) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'overlayopacity' ); ?>"><?php _e( 'Overlay opacity', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/shading.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'overlayopacity' ), $this->get_field_value( 'overlayopacity', '0.7')) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'text2color' ); ?>"><?php _e( 'Title Color', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
			<?php echo $this->get_field('color', $this->get_field_name( 'text2color' ), $this->get_field_value( 'text2color')) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'text1color' ); ?>"><?php _e( 'Description Color', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
			<?php echo $this->get_field('color', $this->get_field_name( 'text1color' ), $this->get_field_value( 'text1color')) ?>
		</div>
	<?php }

	function create_metabox_effects() {
		?>
		<div>
			<label for="<?php echo $this->get_field_id( 'resizeopening' ); ?>" class="hasTooltip" title="<?php _e( 'Resize the lightbox from the following values to the media width when it opens', 'mediabox-ck') ?>"><?php _e( 'Resize transition', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/shape_handles.png" />
			<?php echo $this->get_field('radio', $this->get_field_name( 'resizeopening' ), $this->get_field_value( 'resizeopening', '1'), '', $this->options_yesno) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'resizeduration' ); ?>"><?php _e( 'Resize duration', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/hourglass.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'resizeduration' ), $this->get_field_value( 'resizeduration', '240')) ?>ms
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'initialwidth' ); ?>"><?php _e( 'Initial width', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/width.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'initialwidth' ), $this->get_field_value( 'initialwidth', '320')) ?>px
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'initialheight' ); ?>"><?php _e( 'Initial height', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/height.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'initialheight' ), $this->get_field_value( 'initialheight', '180')) ?>px
		</div>
	<?php }
	
	function create_metabox_mobile() {
		?>
		<div>
			<label for="<?php echo $this->get_field_id( 'mobile_enable' ); ?>" class="hasTooltip" title="<?php _e( 'Switch to a specific layout with full screen for mobile', 'mediabox-ck') ?>"><?php _e( 'Enable for mobile', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/ipod.png" />
			<?php echo $this->get_field('radio', $this->get_field_name( 'mobile_enable' ), $this->get_field_value( 'mobile_enable', '1'), '', $this->options_yesno) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'mobile_detectiontype' ); ?>"><?php _e( 'Detection type', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/wrench_orange.png" />
			<?php $options_attribtype = array(
				'resolution' => __('Resolution')
				, 'tablet' => __('Tablet')
				, 'phone' => __('Phone')
				);
			?>
			<?php echo $this->get_field('select', $this->get_field_name( 'mobile_detectiontype' ), $this->get_field_value( 'mobile_detectiontype', 'resolution' ), '', $options_attribtype) ?>
		</div>
		<div>
			<label for="<?php echo $this->get_field_id( 'mobile_resolution' ); ?>"><?php _e( 'Mobile resolution', 'mediabox-ck'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/width.png" />
			<?php echo $this->get_field('text', $this->get_field_name( 'mobile_resolution' ), $this->get_field_value( 'mobile_resolution', '640')) ?>px
		</div>
	<?php }
	
	function render_options() {
	?>
	<div id="ckwrapper" class="wrap">
		<img src="<?php echo $this->pluginurl ?>/images/logo_mediaboxck_64.png" style="float:left; margin: 0px 5px 5px 0;" />
		<h2><?php esc_attr_e('Mediabox CK Settings', 'mediabox-ck');?></h2>
		<div style="clear:both;"></div>
		<?php echo $this->show_pro_message_settings_page(); ?>
		<p class="noteck"><?php _e('Mediabox CK is a product developped by', 'mediabox-ck') ?> <b><a target="_blank" href="https://profiles.wordpress.org/ced1870/">CEd1870</a></b></p>
		<form method="post" action="options.php">
			<div style="clear:both;">
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Settings', 'mediabox-ck'); ?>" />
			</div>
			<div class="metabox-holder">
				<h2 class="nav-tab-wrapper">
					<a class="menulinkck nav-tab nav-tab-active" tab="tab_general" href="#"><?php _e('General','mediabox-ck'); ?></a>
					<a class="menulinkck nav-tab" tab="tab_styles" href="#"><?php _e('Styles', 'mediabox-ck'); ?></a>
					<a class="menulinkck nav-tab" tab="tab_effects" href="#"><?php echo _e('Effects', 'mediabox-ck'); ?></a>
					<a class="menulinkck nav-tab" tab="tab_mobile" href="#"><?php echo _e('Mobile', 'mediabox-ck'); ?></a>
				</h2>
				<div class="tabck tab-active" id="tab_general">
					<div class="postbox-container" style="width: 99%;">
					<?php 
						settings_fields($this->settings_field); 
						do_meta_boxes( $this->pagehook, 'general', null );
					?>
					</div>
				</div>
				<div class="tabck" id="tab_styles">
					<div class="postbox-container" style="width: 99%;">
					<?php 
						settings_fields($this->settings_field); 
						do_meta_boxes( $this->pagehook, 'styles', null );
					?>
					</div>
				</div>
				<div class="tabck" id="tab_effects">
					<?php 
						settings_fields($this->settings_field); 
						do_meta_boxes( $this->pagehook, 'effects', null );
					?>
				</div>
				<div class="tabck" id="tab_mobile">
					<?php 
						settings_fields($this->settings_field); 
						do_meta_boxes( $this->pagehook, 'mobile', null );
					?>
				</div>
			</div>
			<div>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Settings', 'mediabox-ck'); ?>" />
			</div>
		</form>
		<?php echo $this->show_pro_message_settings_page(); ?>
	</div>
	<!-- Needed to allow metabox layout and close functionality. -->
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function ($) {
			postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			create_nav_tabs($('#ckwrapper'));
			$( '.hasTooltip' ).tooltip({ 
				close: function( event, ui ) {
					ui.tooltip.hide();
				},
				position: {
					my: "left bottom-10",
					at: "left top",
					using: function( position, feedback ) {
						$( this ).css( position );
					}
				},
				track: false,
				tooltipClass: "tooltipckdesc"
			});
		});
		
		function create_nav_tabs(main) {
			jQuery('div.tabck:not(.tab-active)', main).hide();
			jQuery('.menulinkck', main).each(function(i, tab) {
				jQuery(tab).click(function() {
					jQuery('div.tabck', main).hide();
					jQuery('.menulinkck', main).removeClass('nav-tab-active');
					if (jQuery('#' + jQuery(tab).attr('tab')).length)
						jQuery('#' + jQuery(tab).attr('tab')).fadeIn();
					jQuery(this).addClass('nav-tab-active');
				});
			});
		}
		//]]>
	</script>
	<?php }
	
	function get_field($type, $name, $value, $classname = '', $optionsgroup = '') {
		if (!class_exists('Mediaboxck_CKfields'))
			require($this->plugindir . '/includes/class-ckfields.php');
		$ckfields = new Mediaboxck_CKfields();
		$ckfields->pluginurl = $this->pluginurl;
		return $ckfields->get($type, $name, $value, $classname, $optionsgroup);
	}

	function get_field_name( $name ) {
		return sprintf( '%s[%s]', $this->settings_field, $name );
	}
	
	function get_field_id( $name ) {
		return trim(preg_replace('#\W#', '_', $this->get_field_name( $name )), '_');
	}

	function get_field_value( $key, $default = null ) {
		if (isset($this->options[$key])) {
			return $this->options[$key];
		} else {
			if ($default == null && isset($this->default_settings[$key])) 
				return $this->default_settings[$key];
		}
		return $default;
	}

	function show_pro_message_action_links($links, $file) {
		if ($file == 'mediabox-ck/mediabox-ck.php') {
			array_push($links, '<a href="options-general.php?page=' . $this->pluginname . '">'. __('Settings'). '</a>');
			// if (! $this->ispro) {
				// array_push($links, $this->show_pro_message_settings_page());
			// } else {
				// array_push($links, '<br /><img class="iconck" src="' .$this->pluginurl . '/images/tick.png" /><span style="color: green;">' . __('You are using the PRO Version. Thank you !') . '</span>' );
			// }
		}
		return $links;
	}
	
	function show_pro_message_settings_page() {
		return '';
		if (! $this->ispro ) {
			$message = '<div class="ckcheckproversion">
								<img class="iconck" src="' . $this->pluginurl . '/images/star.png" />
								<a target="_blank" href="' . $this->prourl . '">' . __('Get the PRO Version', 'mediabox-ck') . '</a>
						</div>';
		
		} else {
			$message = '<div class="ckcheckproversion">
								<img class="iconck" src="' . $this->pluginurl . '/images/tick.png" />
								<span style="color: green;">' . __('You are using the PRO Version. Thank you !', 'mediabox-ck') . '</span>
						</div>';
		}

		return $message;
	}
}

