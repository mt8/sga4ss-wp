<?php
/**
 * Plugin Name: Simple GA Ranking For Shifter Static
*/
define( 'SGA4SS_GA_UA_ID', 'UA-XXXXXXXXX-X' );
define( 'SGA4SS_GA_DIMENSION_NAME', 'dimensionX' );

define( 'SGA4SS_API_ENDPOINT', 'https://xxxxxxxxxx.execute-api.xx-xxxx-x.amazonaws.com/xxx/sga4ss/' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'jquery');	
});

add_action( 'wp_footer', function () {
    ob_start(); ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html(SGA4SS_GA_UA_ID) ?>"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		<?php if ( is_single() ) : ?>
			gtag('config', '<?php echo esc_html(SGA4SS_GA_UA_ID) ?>', {
				'custom_map': {
					'<?php echo esc_html(SGA4SS_GA_DIMENSION_NAME) ?>': 'post_type'
				},
				'post_type':   'post'
			  });
		<?php else : ?>
			gtag('config', '<?php echo esc_html(SGA4SS_GA_UA_ID) ?>');		
		<?php endif; ?>
	</script>
    <?php echo ob_get_clean();
});

add_shortcode( 'sga4ss',  function( $atts ) {
	ob_start(); ?>
	<div id="sga4ss"></div>
	<script>
		jQuery(function($){
			let api_url = '<?php echo esc_html(SGA4SS_API_ENDPOINT) ?>';
			$.ajax(api_url ).done(function( data ) {
				$('#sga4ss').append( '<ul id="sga4ss-list" ></ul>' );
				for (let i = 0; i < data.rows.length; i++) {
					let a = document.createElement('a');
					a.setAttribute('href',  data.rows[i][0]);
					a.textContent = data.rows[i][1] + '( PV: ' + data.rows[i][2] + ' )';
					let li = document.createElement('li');
					li.appendChild(a);
					$('#sga4ss-list').append(li);
				}
			});
		});
	</script>
	<?php return ob_get_clean();
});

add_filter( 'widget_text', 'do_shortcode' );
