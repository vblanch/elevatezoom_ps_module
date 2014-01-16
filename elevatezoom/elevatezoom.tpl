<!-- Begin elevateZoom Header block -->
<link rel="stylesheet" type="text/css" href="{$content_dir}modules/elevatezoom/css/styles.css" />
<script type="text/javascript" src="{$content_dir}modules/elevatezoom/js/jquery.elevatezoom.min.js"></script>
<script type="text/javascript">			
	var zoom_type = '{$zoom_type}';
	var zoom_fade_in = {$zoom_fade_in};
    var zoom_fade_out = {$zoom_fade_out};
	var zoom_cursor_type = '{$zoom_cursor_type}';
	var zoom_window_pos = {$zoom_window_pos};
	var zoom_scroll = {$zoom_scroll};
	var zoom_easing = {$zoom_easing};
	var zoom_tint = {$zoom_tint};
	var zoom_tint_color = '{$zoom_tint_color}';
	var zoom_tint_opacity = {$zoom_tint_opacity};
    var zoom_lens_shape = '{$zoom_lens_shape}';
    var zoom_lens_size  = {$zoom_lens_size};
</script>
<script type="text/javascript">
{if $zoom_product==1}
	function applyElevateZoom(){
		var bigimage = $('.thickbox.shown').attr('href');
		$('#bigpic').elevateZoom({
			zoomType: zoom_type,
			cursor: zoom_cursor_type,
			zoomWindowFadeIn: zoom_fade_in,
			zoomWindowFadeOut: zoom_fade_out,
			zoomWindowPosition: zoom_window_pos,
			scrollZoom: zoom_scroll,
			easing: zoom_easing,
			tint: zoom_tint,
			tintColour: zoom_tint_color,
			tintOpacity: zoom_tint_opacity,
			lensShape: zoom_lens_shape,
			lensSize: zoom_lens_size,
			zoomImage: bigimage{if $zoom_extra_params|strip!=''},
			{$zoom_extra_params} {/if}
	   });
	}
	function restartElevateZoom(){
		$(".zoomContainer").remove();
		applyElevateZoom();
	}
	$(document).ready(function(){
		applyElevateZoom();
		$('#views_block li a').hover(
			function(){
				restartElevateZoom();
			},function(){}
		);
		$('#color_to_pick_list a').click(function(){
			restartElevateZoom();
		});
	});
{/if}
{if $zoom_other==1}
	$(document).ready(function(){
		{$zoom_other_code}
	});
{/if}
</script>
<!-- End elevateZoom Header block -->