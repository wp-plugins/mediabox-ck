<?php
defined('ABSPATH') or die;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}

class MediaboxckFront {

	function __construct() {
		$this->pluginname = 'mediabox-ck';
		$this->pluginurl = plugins_url( '', __FILE__ );
		$this->plugindir = plugin_dir_path( __FILE__ );
		$this->settings_field = 'mediaboxck_options';
		$this->options = get_option( $this->settings_field );

		add_action('wp_head', array( $this, 'load_assets'));
		add_action('init', array( $this, 'load_assets_files'));
		/*add_action('template_redirect',array( $this, 'do_my_ob_start') );*/
	}

	/*function do_my_ob_start() {
		ob_start(array($this, 'search_key') );
	}*/

	function load_assets_files() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core ');
		wp_enqueue_style('mediaboxck', $this->pluginurl . '/assets/mediaboxck.css');
		wp_enqueue_script('mediaboxck', $this->pluginurl . '/assets/mediaboxck.min.js');
	}

	function load_assets() {
		// mobile detection
		if (!class_exists('Mediaboxck_Mobile_Detect')) {
			require_once $this->plugindir . 'includes/mediaboxck_mobile_detect.php';
		}
		$detect = new Mediaboxck_Mobile_Detect;
		$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
		
		$cornerradius = $this->get_option('cornerradius', '10');
		$shadowoffset = $this->get_option('shadowoffset', '5');
		$overlayopacity = $this->get_option('overlayopacity', '0.7');
		$bgcolor = $this->get_option('bgcolor', '#1a1a1a');
		$overlaycolor = $this->get_option('overlaycolor', '#000');
		$text1color = $this->get_option('text1color', '#999');
		$text2color = $this->get_option('text2color', '#fff');
		$resizeopening = $this->get_option('resizeopening', 'true');
		$resizeduration = $this->get_option('resizeduration', '240');
		$initialwidth = $this->get_option('initialwidth', '320');
		$initialheight = $this->get_option('initialheight', '180');
		$defaultwidth = $this->get_option('defaultwidth', '640');
		$defaultheight = $this->get_option('defaultheight', '360');
		$showcaption = $this->get_option('showcaption', 'true');
		$showcounter = $this->get_option('showcounter', 'true');
		$attribtype = $this->get_option('attribtype', 'rel');
		$attribname = $this->get_option('attribname', 'lightbox');

		$css = "#mbCenter {
					background-color: ".$bgcolor.";
					-webkit-border-radius: ".$cornerradius."px;
					-khtml-border-radius: ".$cornerradius."px;
					-moz-border-radius: ".$cornerradius."px;
					border-radius: ".$cornerradius."px;
					-webkit-box-shadow: 0px ".$shadowoffset."px 20px rgba(0,0,0,0.50);
					-khtml-box-shadow: 0px ".$shadowoffset."px 20px rgba(0,0,0,0.50);
					-moz-box-shadow: 0px ".$shadowoffset."px 20px rgba(0,0,0,0.50);
					box-shadow: 0px ".$shadowoffset."px 20px rgba(0,0,0,0.50);
					/* For IE 8 */
					-ms-filter: \"progid:DXImageTransform.Microsoft.Shadow(Strength=".$shadowoffset.", Direction=180, Color='#000000')\";
					/* For IE 5.5 - 7 */
					filter: progid:DXImageTransform.Microsoft.Shadow(Strength=".$shadowoffset.", Direction=180, Color='#000000');
					}
					
					#mbOverlay {
						background-color: ".$overlaycolor.";
					}
					
					#mbCenter.mbLoading {
						background-color: ".$bgcolor.";
					}
					
					#mbBottom {
						color: ".$text1color.";
					}
					
					#mbTitle, #mbPrevLink, #mbNextLink, #mbCloseLink {
						color: ".$text2color.";
					}
				";
		?>
		<script type="text/javascript">
		Mediabox.scanPage = function() {
			var links = jQuery('a').filter(function(i) {
				if ( jQuery(this).attr('<?php echo ($attribtype == 'rel' ? 'rel' : 'class') ?>') 
						&& jQuery(this).attr('mediaboxck_done') != '1') {
					var patt = new RegExp(/^lightbox/i);
					return patt.test(jQuery(this).attr('<?php echo ($attribtype == 'rel' ? 'rel' : 'class') ?>'));
				}
			});
			if (! links.length) return;

			links.mediabox({
			overlayOpacity : 	<?php echo $overlayopacity ?>,
			resizeOpening : 	<?php echo $resizeopening ?>,
			resizeDuration : 	<?php echo $resizeduration ?>,
			initialWidth : 		<?php echo $initialwidth ?>,
			initialHeight : 	<?php echo $initialheight ?>,
			defaultWidth : 		<?php echo $defaultwidth ?>,
			defaultHeight : 	<?php echo $defaultheight ?>,
			showCaption : 		<?php echo $showcaption ?>,
			showCounter : 		<?php echo $showcounter ?>,
			loop : 				<?php echo $this->get_option('loop', 'false') ?>,
			isMobileEnable: 	<?php echo $this->get_option('mobile_enable', '1') ?>,
			mobileDetection: 	'<?php echo $this->get_option('mobile_detectiontype', 'resolution') ?>',
			isMobile: 			<?php echo ($deviceType != 'computer' ? 'true' : 'false')  ?>,
			mobileResolution: 	'<?php echo $this->get_option('mobile_resolution', '640') ?>',
			attribType :		'<?php echo ($attribtype == 'rel' ? 'rel' : 'class') ?>',
			playerpath: '<?php echo $this->pluginurl ?>/assets/NonverBlaster.swf'
			}, null, function(curlink, el) {
				var rel0 = curlink.<?php echo $attribtype ?>.replace(/[[]|]/gi," ");
				var relsize = rel0.split(" ");
				return (curlink == el) || ((curlink.<?php echo $attribtype ?>.length > <?php echo strlen($attribname) ?>) && el.<?php echo $attribtype ?>.match(relsize[1]));
			});
		};
		jQuery(document).ready(function(){ Mediabox.scanPage(); });
		</script>
		<style type="text/css">
		<?php echo $css; ?>
		</style>
	<?php }

	function get_option($name, $default = '') {
		if (isset($this->options[$name])) {
			return $this->options[$name];
		} else if (isset($this->default_settings[$name])) {
			return $this->default_settings[$name];
		} else {
			return $default;
		}
		return null;
	}

	/*function search_key($content){
		// test if the plugin is needed
		if (!stristr($content, "{tooltip}"))
			return $content;
		
		$regex = "#{tooltip}(.*?){end-tooltip}#s"; // search mask
		$content = preg_replace_callback($regex, array('Mediaboxck_Front', 'create_tooltip'), $content);

		return $content;
	}*/

	/*function create_tooltip(&$matches) {
		$ID = (int) (microtime() * 100000); // unique ID
		$stylewidth = $this->get_option('stylewidth');
		$fxduration = $this->get_option('fxduration');
		$dureebulle = $this->get_option('dureebulle');
		$tipoffsetx = $this->get_option('tipoffsetx');
		$tipoffsety = $this->get_option('tipoffsety');

		// get the text
		$patterns = "#{tooltip}(.*){(.*)}(.*){end-tooltip}#Uis";
		$result = preg_match($patterns, $matches[0], $results);

		// check if there is some custom params
		$relparams = Array();
		$params = explode('|', $results[2]);
		$parmsnumb = count($params);
		for ($i = 1; $i < $parmsnumb; $i++) {
			$fxduration = stristr($params[$i], "mood=") ? str_replace('mood=', '', $params[$i]) : $fxduration;
			$dureebulle = stristr($params[$i], "tipd=") ? str_replace('tipd=', '', $params[$i]) : $dureebulle;
			$tipoffsetx = stristr($params[$i], "offsetx=") ? str_replace('offsetx=', '', $params[$i]) : $tipoffsetx;
			$tipoffsety = stristr($params[$i], "offsety=") ? str_replace('offsety=', '', $params[$i]) : $tipoffsety;
			$stylewidth = stristr($params[$i], "w=") ? str_replace('px', '', str_replace('w=', '', $params[$i])) : $stylewidth;
		}

		// compile the rel attribute to inject the specific params
		$relparams['mood'] = 'mood=' . $fxduration;
		$relparams['tipd'] = 'tipd=' . $dureebulle;
		$relparams['offsetx'] = 'offsetx=' . $tipoffsetx;
		$relparams['offsety'] = 'offsety=' . $tipoffsety;

		$tooltiprel = '';
		if (count($relparams)) {
			$tooltiprel = ' rel="' . implode("|", $relparams) . '"';
		}

		// output the code
		$result = '<span class="infotip" id="mediaboxck' . $ID . '"' . $tooltiprel . '>'
					. $results[1]
					. '<span class="mediaboxck_tooltip" style="width:' . $stylewidth . 'px;">'
						. '<span class="mediaboxck_inner">'
						. $results[3]
						. '</span>'
					. '</span>'
				. '</span>';

		return $result;
	}*/
}
