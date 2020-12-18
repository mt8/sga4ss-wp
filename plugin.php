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
			$.ajaxSetup({ cache: false });
			let api_url = '<?php echo esc_html(SGA4SS_API_ENDPOINT) ?>';
			$.ajax(api_url ).done(function( data ) {
				$('#sga4ss').append( '<ul id="sga4ss-list" ></ul>' );
				$.ajaxSetup({ cache: true });
				for (let i = 0; i < data.rows.length; i++) {
					let post_id = data.rows[i][0].replace('/', '');
					let a = document.createElement('a');
					a.setAttribute('href',  data.rows[i][0]);
					a.textContent = data.rows[i][1] + '( PV: ' + data.rows[i][2] + ' )';
					let li = document.createElement('li');
					$.ajaxSetup({async: false});
					$.getJSON("/wp-json/wp/v2/posts/" + post_id, function(data) {
						let img = document.createElement('img');
						img.setAttribute('src', data.sga4ss_featured_media_url);
						a.appendChild(img);
					});
					$.ajaxSetup({async: true});
					li.appendChild(a);
					$('#sga4ss-list').append(li);
				}
			});
		});
	</script>
	<?php return ob_get_clean();
});

add_filter( 'widget_text', 'do_shortcode' );

add_action( 'init', function(){
    add_filter( 'ShifterURLS::AppendURLtoAll', 'my_append_urls' );
} );
function my_append_urls( $urls ) {
		$posts = get_posts(
			array(
				'post_type' => 'post',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
			)
		);
		foreach ( $posts as $post ) {
			if ( ! has_post_thumbnail( $post ) ) {
				continue;
			}
			$thumb_id = get_post_thumbnail_id( $post );
			$urls[] = home_url( "/wp-json/wp/v2/posts/{$post}/" );			
		}
		return $urls;
}

add_action( 'rest_api_init', 'sga4ss_add_featured_media_url' );
function sga4ss_add_featured_media_url() {
		register_rest_field( 'post', 'sga4ss_featured_media_url',
			array(
				'get_callback'    => 'sga4ss_featured_media_url',
				'update_callback' => null,
				'schema'          => null,
			)
		);
}

function sga4ss_featured_media_url( $object, $field_name, $request ) {
		$featured_media_url = '';
		$image_attributes = wp_get_attachment_image_src(
			get_post_thumbnail_id( $object['id'] ),
			'full'
		);
		if ( is_array( $image_attributes ) && isset( $image_attributes[0] ) ) {
			$featured_media_url = (string) $image_attributes[0];
		}
		return $featured_media_url;
}