<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'mod_sellacious_usersonmap/style.css', null, true);

JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCXKLeipN-wqAUxIBWZGFaBF60P2duX-fU', false, false);
JHtml::_('script', 'mod_sellacious_usersonmap/script.js', false, true);
?>
<div class="mod-sellacious-usersonmap <?php echo $classSfx; ?>" id="mod-sellacious-usersonmap">
	<?php if (!empty($markers)) : ?>
		<script type="text/javascript">
			jQuery(window).load(function($) {
				var markers = <?php echo json_encode($markers); ?>;
				var zoom = <?php echo intval($zoom); ?>;
				var location = [<?php echo $location; ?>];

				var myMap = new ModSellaciousUsersOnMap;
				myMap.initialize(markers, zoom, location);
			});
		</script>

		<div id="dvMap" style="width: auto; height: 400px"></div>
	<?php else: ?>
		<div style="text-align: center"><b>No Users Found</b></div>
	<?php endif; ?>
</div>
<div class="clearfix"></div>
