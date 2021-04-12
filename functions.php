<?php
# require get_template_directory() . "/inc/template-tags.php";

add_action("wp_enqueue_scripts", "silvia_genoves_enqueue_scripts");
function silvia_genoves_enqueue_scripts () {
    wp_enqueue_style(
        "silvia-genoves-style",
        get_stylesheet_uri(),
        array(
            "eksell-style"
        ),
        wp_get_theme()->get("Version")
    );

    /* wp_register_script(
	    "silvia-genoves-dropdown",
            get_stylesheet_directory_uri() . "/js/dropdown.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-dropdown"); */

    wp_register_script(
	    "silvia-genoves-breadcrumb",
            get_stylesheet_directory_uri() . "/js/breadcrumb.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-breadcrumb");

    wp_register_script(
	    "silvia-genoves-custom-filters",
	    get_stylesheet_directory_uri() . "/js/custom_filters.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-custom-filters");
    wp_localize_script("silvia-genoves-custom-filters", "sg_ajax_filters", array(
	"ajaxurl" => esc_url(admin_url("admin-ajax.php"))
    ));
}

add_action("pre_get_posts", "silvia_genoves_sort_reversed");
function silvia_genoves_sort_reversed ($query) {
    if ($query->is_home() && $query->is_main_query()) {
        $query->set("order", "ASC");
        $query->set("category_name", "Fotografía");
    } else if ($query->is_category()) {
	$query->set("order", "ASC");
    }
}

add_action("eksell_archive_header_end", "eksell_the_archive_filter");
function eksell_the_archive_filter () {

	// Check if we're showing the filter
	if (!eksell_show_home_filter()) return;

	$terms = sg_get_filter_terms();

	if (!$terms) return;

	$home_url = "";
	$post_type 	= "";

	// Determine the correct home URL to link to.
	if (is_home()) {
		$post_type = "post";
		$home_url = home_url();
	} elseif (is_post_type_archive()) {
		$post_type	= get_post_type();
		$home_url = get_post_type_archive_link($post_type);
	}

	// Make the home URL filterable. If you change the taxonomy of the filtration with `eksell_home_filter_get_terms_args`,
	// you might want to filter this to make sure it points to the correct URL as well (or maybe remove it altogether).
	$home_url = apply_filters("eksell_filter_home_url", $home_url); ?>
		<div class="filter-wrapper i-a a-fade-up a-del-200">
			<ul class="filter-list reset-list-style"><!--
			<?php if ($home_url) : ?>
				<li><a class="filter-link active" data-filter-post-type="<?php echo esc_attr($post_type);?>" href="<?php echo esc_url($home_url);?>"><?php esc_html_e("Show At", "eksell"); ?></a></li>
            <?php endif; ?> -->
			<?php
            $is_first = true;
            # foreach ($terms as $term) :
	    foreach ($terms["main"] as $term) :
                if ($term->slug != "joyeria") : ?>
                <li><a class="filter-link <?php echo $is_first ? 'active' : ''; ?>" data-filter-term-id="<?php echo esc_attr( $term->term_id ); ?>" data-filter-taxonomy="<?php echo esc_attr( $term->taxonomy ); ?>" data-filter-post-type="<?php echo esc_attr( $post_type ); ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo $term->name; ?></a></li>
                <?php else : ?>
                <li class="filter-link-parent">
                    <a class="filter-link" data-filter-term-id="<?php echo esc_attr($term->term_id); ?>" data-filter-taxonomy="<?php echo esc_attr($term->taxonomy); ?>" data-filter-post-type="<?php echo esc_attr($post_type); ?>" href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>
                    <ul class="filter-link-menu">
                    <?php # foreach ($nested_terms as $term) : ?>
		    <?php foreach ($terms["nested"] as $term) : ?>
                        <li><a class="filter-link" data-filter-term-id="<?php echo esc_attr($term->term_id); ?>" data-filter-taxonomy="<?php echo esc_attr($term->taxonomy); ?>" data-filter-post-type="<?php echo esc_attr($post_type); ?>" href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a></li>
                    <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; $is_first = false; ?>
			<?php endforeach; ?>
		</ul>
	</div> <?php
}

function sg_get_filter_terms () {
	$db_terms = get_terms(apply_filters("eksell_home_filter_get_terms_args", array(
		"depth" => 1,
		"taxonomy" => "category"
	)));

    	$terms_order = array("fotografia", "espectaculos", "plastica", "joyeria");
    	$nested_terms_order = array("oro-plata-y-piedras preciosas", "resina", "resina-y-plata", "resina-y-piedras-semipreciosas");

    	$terms = array();
    	$nested_terms = array();
    	foreach ($db_terms as $term) {
        	$index = array_search($term->slug, $terms_order);
        	if ($index !== false) {
            		$terms[$index] = $term;
        	} else if ($term->parent) {
            		$index = array_search($term->slug, $nested_terms_order);
       		     	$nested_terms[$index] = $term;
        	}
    	}

    	ksort($terms);
	ksort($nested_terms);

	return array(
		"main" => $terms,
		"nested" => $nested_terms
	);
}

function sg_aside_filters () {
	$terms = sg_get_filter_terms();
	$category = get_query_var("category");
	if ($_GET && !empty($_GET) && isset($_GET["cat"])) {
		$cat = $_GET["cat"];
	} else if ($_GET && !empty($_GET) && isset($_GET["page_id"])) {
		$cat = null;	
	} else {
		$cat = 3;
	}

	foreach ($terms["main"] as $term) : ?>
		<li class="page_item <?php echo $term->term_id == $cat ? "current_page_item" : ""; ?>">
		<a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>
		<?php if ($term->slug == "joyeria") : ?>
		<ul class="nested-menu reset-list-style">
		<?php foreach ($terms["nested"] as $term) : ?>
			<li class="page_item page_item_nested <?php echo $term->term_id == $cat ? "current_page_item" : ""; ?>">
				<a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>	
			</li>
		<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</li>
	<?php endforeach;
}

function eksell_ajax_filters () {

	// Get the filters from AJAX.
	$term_id = isset($_POST["term_id"]) ? $_POST["term_id"] : null;
	$taxonomy = isset($_POST["taxonomy"]) ? $_POST["taxonomy"] : "";
	$post_type = isset($_POST["post_type"]) ? $_POST["post_type"] : "";

	$args = array(
		"ignore_sticky_posts" => false,
		"post_status" => "publish",
		"post_type" => $post_type,
        	"order" => "ASC"
	);

	if ($term_id && $taxonomy) {
		$args["tax_query"] = array(array(
			"taxonomy"=> $taxonomy,
			"terms" => $term_id
		));
	}

	$custom_query = new WP_Query($args);

	// Combine the query with the query_vars into a single array.
	$query_args = array_merge($custom_query->query, $custom_query->query_vars);

	// If max_num_pages is not already set, add it.
	if (!array_key_exists("max_num_pages", $query_args)) {
		$query_args["max_num_pages"] = $custom_query->max_num_pages;
	}

	// Format and return.
	echo json_encode( $query_args );

	wp_die();
}

add_action("wp_ajax_nopriv_eksell_ajax_filters", "eksell_ajax_filters");
add_action("wp_ajax_eksell_ajax_filters", "eksell_ajax_filters");


function eksell_ajax_load_more () {
	$query_args = json_decode(wp_unslash($_POST["json_data"]), true);

	$ajax_query = new WP_Query($query_args);

	// Determine which preview to use based on the post_type.
	$post_type = $ajax_query->get("post_type");

	// Default to the "post" post type for mixed content.
	if ( !$post_type || is_array($post_type)) {
		$post_type = "post";
	}

	if ($ajax_query->have_posts()) :
		while ($ajax_query->have_posts()) : 
			$ajax_query->the_post();
			global $post;

			$tags = wp_get_post_tags($post->ID);
			$span = count($tags) > 0;
			if ($span) {
				foreach ($tags as $tag) {
					if ($tag->slug == "col-3") {
						$span = 3;
					} else if ($tag->slug == "col-2") {
						$span = 2;
					} else {
						$span = 1;
					}
				}
			}
			?>
			<div class="article-wrapper col <?php echo $span ? "col-span-{$span}" : ""; ?>">
				<?php get_template_part("inc/parts/preview", $post_type); ?>
			</div>
			<?php 
		endwhile;
	endif;
	wp_die();
}

add_action("wp_ajax_nopriv_eksell_ajax_load_more", "eksell_ajax_load_more");
add_action("wp_ajax_eksell_ajax_load_more", "eksell_ajax_load_more");
?>
