<?php 

/*
Plugin Name: Per Category Widgets
Plugin URI: http://aramzs.me
Description: This plugin allows users to attach sidebars to a category in a way that respects their hierarchy.
Version: 0.0.1
Author: Aram Zucker-Scharff
Author URI: http://aramzs.me
License: GPL2
*/

/*  Developed for SES

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PCW {

	function __construct() {
		add_action('init', array($this,'register_scripts') );
		add_action('init', array($this,'register_styles') );
		
		add_filter( 'manage_edit-category_columns', array($this, 'manage_sidebars_by_cat'), 10);
		add_filter( 'manage_category_custom_column', array($this, 'manage_sidebars_by_cat_column'), 10,3);
		add_action( 'category_edit_form_fields', array($this, 'sidebars_by_cat_ui'), 10,2);
		add_action( 'edit_category', array($this, 'verify_sidebars_by_cat_ui'));
	}	
	
	public static function register_scripts(){

	}

	public static function register_styles(){
		wp_register_script('select2', plugins_url('library/select2/select2.js', __FILE__), array('jquery'), '3.4.6');
		wp_register_style( 'select2', plugins_url('library/select2/select2.css', __FILE__ ));
	}
	
	public static function manage_sidebars_by_cat($columns){
	
		$cb = $columns['name'];
		$name = $columns['name'];
		unset( $columns['cb'], $columns['name'] );

		$columns = array_reverse( $columns, true );

		$columns['pcwm_category_sidebar_override'] = __( 'Override default sidebar', 'pcw' );
		$columns['pcwm_category_sidebar'] = __( 'Category sidebar', 'pcw' );
		$columns['name'] = $name;
		$columns['cb'] = $cb;

		$columns = array_reverse( $columns, true );

		return $columns;
	
	}
	
	public static function manage_sidebars_by_cat_column( $row, $column_name, $term_id ){
		if ( ! in_array( $column_name, array( 'pcwm_category_sidebar', 'pcwm_category_sidebar_override' ) ) )
			return $row;

		if ( 'pcwm_category_sidebar' == $column_name )
			$post_id = self::get_category _sidebar( $term_id );
		else
			$post_id = self::get_pcwm_category_sidebar_override( $term_id );

		if ( $post = get_post( $post_id ) )
			return $post->post_title;
		else
			return '<em>' . __( 'None', 'pcw' ) . '</em>';
	}

	public static function enqueue(){
		wp_enqueue_style('select2');
		wp_enqueue_script('select2');
	}
	
	public static function sidebars_by_cat_ui( $term, $taxonomy ){
		add_action('wp_enqueue_scripts', array($this,'enqueue') );
		$featured_article = get_post( self::get_pcwm_category_sidebar( $term->term_id ) );
		if ( $featured_article ) {
			$id = $featured_article->ID;
			$title = $featured_article->post_title;
		} else {
			$id = $title = false;
		}

		$featured_widget_article = get_post( self::get_pcwm_category_sidebar_override( $term->term_id ) );
		if ( $featured_widget_article ) {
			$widget_id = $featured_widget_article->ID;
			$widget_title = $featured_widget_article->post_title;
		} else {
			$widget_id = $widget_title = false;
		}
		?>
		<tr class="form-field hide-if-no-js">
			<th scope="row" valign="top"><label for="description"><?php  esc_html_e( 'Category Sidebar', 'pcwmpcwm' ) ?></label></th>
			<td>
				<input type="text" class="pcwm-category-sidebar" name="pcwm-category-sidebar" value="<?php echo (int)$id; ?>" title="<?php echo esc_attr( $title ); ?>" />
			</td>
		</tr>

		<tr class="form-field hide-if-no-js">
			<th scope="row" valign="top"><label for="description"><?php  esc_html_e( 'Category Sidebar Override', 'pcwm' ) ?></label></th>
			<td>
				<input type="text" class="pcwm-category-sidebar-override" name="pcwm-category-sidebar-override" value="<?php echo (int)$widget_id; ?>" title="<?php echo esc_attr( $widget_title ); ?>" />
			</td>
		</tr>

		<script>
		jQuery(document).ready(function($){

			var select2Options = {
				allowClear: true,
				placeholder: "<?php _e( 'Search Sidebars', 'pcwm' ); ?>",
				minimumInputLength: 3,
				width: 'element',
				ajax: {
					url: ajaxurl,
					dataType: 'json',
					data: function( term, page ) {
						return {
							s: term,
							category_id: '<?php echo $term->term_id; ?>',
							action: 'pcwm_search_sidebars',
						};
					},
					results: function( data, page ) {
						return { results: data };
					},
				},
				initSelection: function( element, callback ) {
					callback({id: $(element).val(), text: $(element).attr('title' ) } );
				}
			};

			$('.pcwm-category-sidebar').select2(select2Options);

		});
		</script>
		<?php
	}
	
	public static function verify_sidebars_by_cat_ui($term_id){
	

		if ( isset( $_POST['pcwm_category_sidebar'] ) ) {
			$featured_articles = get_option( 'pcwm_category_sidebar', array() );
			$featured_articles[$term_id] = (int)$_POST['pcwm-category-sidebar'];
			update_option( 'pcwm_category_sidebar', $featured_articles );
		}

		if ( isset( $_POST['pcwm_category_sidebar_override'] ) ) {
			$featured_articles = get_option( 'pcwm_category_sidebar_override', array() );
			$featured_articles[$term_id] = (int)$_POST['pcwm-category-sidebar-override'];
			update_option( 'pcwm_category_sidebar_override', $featured_articles );
		}		
	
	}
	
	public static function get_pcwm_category_sidebar($term_id){
		$sidebar = get_option('pcwm_category_sidebar',array());
		if (empty($sidebar[$term_id])){
			return false;
		} else {
			return $sidebar[$term_id];
		}
	}
	
	public static function get_pcwm_category_sidebar_override($term_id){
		$sidebar = get_option('pcwm_category_sidebar_override',array());
		if (empty($sidebar[$term_id])){
			return false;
		} else {
			return $sidebar[$term_id];
		}	
	}
}

/**
 * Bootstrap
 *
 * You can also use this to get a value out of the global, eg
 *
 *    $foo = PCW()->bar;
 *
 * @since 1.7
 */
function pcw() {
	global $pcw;
	if ( ! is_a( $pcw, 'PCW' ) ) {
		$pcw = new PCW();
	}
	return $pcw;
}

// Start me up!
pcw();