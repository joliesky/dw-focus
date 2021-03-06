<?php  
/**
 * Recent_Posts widget class
 *
 * @since 2.8.0
 */
class dw_focus_recents_posts_Widget extends WP_Widget {

    function widget($args, $instance) {

        $cache = wp_cache_get('widget_dw_focus_recent_posts', 'widget');

        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

        ob_start();
        extract($args);

        $title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '', $instance, $this->id_base);
        $thumbnail_size = isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : 'thumbnail';
        
        echo $before_widget;
        if ( !empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }
        $r = $this->query($instance);
        global $more;
        if ( $r->have_posts() ) :
                while ($r->have_posts()) : $r->the_post(); 
                $class = 'item';
                $post_id = get_the_ID();
            	date_default_timezone_set('America/Denver');
		$today = strtotime("today");
		$now = strtotime("now");
	    	$updatetime = get_post_meta( get_the_ID(), 'updatetime', true ); 
	   	$breaking_pre = get_post_meta( get_the_ID(), 'journal_breaking_pre', true );
	    ?>
            <article <?php post_class($class); ?> >
                <h2 class="entry-title">
			<!-- JM - adds updated/breaking before link if update time is diff than post time/check box is checked -->
			<?php if($updatetime != get_the_time('U') && ($updatetime > get_the_time('U') + 600)) { ?>
				<span class="updatedtime">Updated: </span>
			<?php } else if(( $breaking_pre == 1 ) && (($now - get_the_date('U')) < 43200)) { ?>
				<span class="updatedtime" style="color: blue !important;">Breaking: </span>
			<?php } ?>
			<a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
                	<!-- JM - ads time if was posted or updated today, with specifications -->
			<?php if( isset( $instance['meta'] ) && $instance['meta'] ) { ?>
                    		<?php if( isset( $instance['date'] ) && $instance['date'] ) {
					if($updatetime >= ($today + 1800)) {
                                                if($updatetime >= ($now - 3600)) { ?>
                                                        <? $dw_updatetime = strftime('%l:%M %P', $updatetime); ?>
							<span class="latesttime red"> - <?php echo dw_focus_time_stamp($dw_updatetime); ?></span>
                                                <?php } else { ?>
                                                        <span class="latesttime"> - <?php echo date('g:i a', $updatetime); ?></span>
                                                <?php }
                                        }
                   		}
                	} ?>
		</h2>   
		

                <?php if( isset($instance['post_excerpt']) && $instance['post_excerpt'] ) {  ?>
                <div class="entry-content"><?php the_excerpt(); ?></div>
                <?php } ?>
            </article>

            <?php endwhile; ?>
        <?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata(); ?>
        
	<h2 class="entry-title" style="text-align: right; font-style: italic;"><a href="/category/abqnewsseeker">More articles &raquo;</a></h2>
        
	<?php else:
            get_template_part('no-results');
        endif;

        echo $after_widget;
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_recent_posts', $cache, 'widget');
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['category'] = (int) $new_instance['category'];
        $instance['thumbnail'] = (bool) $new_instance['thumbnail'];
        $instance['meta'] = (bool) $new_instance['meta'];
        $instance['date'] = (bool) $new_instance['date'];
        $instance['author'] = (bool) $new_instance['author'];
        $instance['cat'] = (bool) $new_instance['cat'];
        $instance['post_excerpt'] = (bool) $new_instance['post_excerpt'];
        $instance['post-format'] =  $new_instance['post-format'];
        $instance['thumbnail_size'] = $new_instance['thumbnail_size'];
	$this->flush_widget_cache();

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_recent_entries']) )
            delete_option('widget_recent_entries');

        return $instance;
    }

    function flush_widget_cache() {
        wp_cache_delete('widget_recent_posts', 'widget');
    }

    function form( $instance ) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 5;
        $category = isset($instance['category']) ? absint($instance['category']) : 0;
        $thumbnail = isset($instance['thumbnail']) ? (bool) $instance['thumbnail'] : false;
        $post_format = isset($instance['post-format']) ? $instance['post-format'] : '';
        $meta = isset($instance['meta']) ? (bool) $instance['meta'] : false;

        $date = isset($instance['date']) ? (bool) $instance['date'] : false;


        $author = isset($instance['author']) ? (bool) $instance['author'] : false;

        $cat = isset($instance['cat']) ? (bool) $instance['cat'] : false;

        $post_excerpt = isset($instance['post_excerpt']) ? (bool) $instance['post_excerpt'] : false;

        $thumbnail_size = isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : 'medium';
?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','dw_focus'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'dw_focus' ); ?></label>
        <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
        <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'dw_focus' ); ?></label><br>
            <?php wp_dropdown_categories(array(
                'show_option_all'   =>  'All categories',
                'hide_empty'        =>  0,
                'id'                =>  $this->get_field_id('category'),
                'name'              =>  $this->get_field_name('category'),
                'selected'          =>  $category,
                'class'             =>  'widefat',
                'hierarchical'      =>  true,
                'walker'            =>  new DW_Walker_CategoryDropdown()
            ) ); ?>
        </p>
        <p><label for="<?php echo $this->get_field_id('post-format'); ?>"><?php _e('Post Format:', 'dw_focus'); ?></label><br>
           <select class="widefat" name="<?php echo $this->get_field_name('post-format') ?>" id="<?php echo $this->get_field_id('post-format')  ?>">
                <option <?php selected($post_format, '') ?> value="">All</option>
                <option <?php selected($post_format, 'video') ?> value="video">Video</option>
                <option <?php selected($post_format, 'gallery') ?> value="gallery">Gallery</option>
                <option <?php selected($post_format, 'audio') ?> value="audio">Audio</option>
              
            </select>   
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail'); ?>" ><input type="checkbox" name="<?php echo $this->get_field_name('thumbnail') ?>" id="<?php echo $this->get_field_id('thumbnail'); ?>" <?php checked(true, $thumbnail); ?>>&nbsp;&nbsp;<?php  _e('Show thumbnail for first post only','dw-focus') ?> </label>
        </p>

        <!-- Meta info -->
        <div class="meta-info">
            <p>
                <label for="<?php echo $this->get_field_id('meta'); ?>" ><input type="checkbox" name="<?php echo $this->get_field_name('meta') ?>" id="<?php echo $this->get_field_id('meta'); ?>" <?php checked(true, $meta); ?> class="recent-post-meta-info" >&nbsp;&nbsp;<?php  _e('Show the meta infomation of post','dw-focus') ?> </label>
            </p>
            <p> --
                <label for="<?php echo $this->get_field_id('date'); ?>" ><input type="checkbox" <?php disabled( false,  $meta ); ?> name="<?php echo $this->get_field_name('date') ?>" id="<?php echo $this->get_field_id('date'); ?>" <?php checked(true, $date); ?> class="submeta-info" >&nbsp;&nbsp;<?php  _e('Show the date of post','dw-focus') ?> </label>
            </p>
            <p> --
                <label for="<?php echo $this->get_field_id('author'); ?>" ><input type="checkbox" <?php disabled( false,  $meta ); ?> name="<?php echo $this->get_field_name('author') ?>" id="<?php echo $this->get_field_id('author'); ?>" <?php checked(true, $author); ?> class="submeta-info" >&nbsp;&nbsp;<?php  _e('Show the author info','dw-focus') ?> </label>
            </p>
            <p> --
                <label for="<?php echo $this->get_field_id('cat'); ?>" ><input type="checkbox" <?php disabled( false,  $meta ); ?> name="<?php echo $this->get_field_name('cat') ?>" id="<?php echo $this->get_field_id('cat'); ?>" <?php checked(true, $cat); ?> class="submeta-info" >&nbsp;&nbsp;<?php  _e('Show the category info','dw-focus') ?> </label>
            </p>
        </div>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_size') ?>">
                <?php  _e('Choose an image size','dw-focus') ?>
                <select class="widefat" name="<?php echo $this->get_field_name('thumbnail_size') ?>" id="<?php echo $this->get_field_id('thumbnail_size')  ?>">
                    <option <?php selected($thumbnail_size, 'large') ?> value="large">Large</option>
                    <option <?php selected($thumbnail_size, 'medium') ?> value="medium">Medium</option>
                    <option <?php selected($thumbnail_size, 'thumbnail') ?> value="thumbnail">Thumbnail</option>
                  
                </select>   
            </label>
        </p>
        <p><label for="<?php echo $this->get_field_id('post_excerpt'); ?>"><input type="checkbox" name="<?php echo $this->get_field_name('post_excerpt'); ?>" id="<?php echo $this->get_field_id('post_excerpt'); ?>" <?php checked( true, $post_excerpt ); ?> > <?php _e('Show readmore content', 'dw_focus'); ?></label></p>  
<?php
    }

    function query($instance){

        $category = $instance['category'];

        if( is_category() && dw_focus_sidebar_has_widget( 'dw_focus_category_sidebar', $this->id ) ){

            $category = get_query_var( 'cat' );
        }

        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
            $number = 10;
        $tax_query=array();
        if(!empty($instance['post-format'])){
             $tax_query= array(
                            array(
                                'taxonomy' => 'post_format',
                                'field'    => 'slug',
                                'terms'    => array( 'post-format-'.$instance['post-format']),
                                'operator' => 'IN'
                            )
                          );
             }
        return new WP_Query( apply_filters( 'widget_posts_args', 
            array( 
                'posts_per_page'    => $number, 
                'no_found_rows'         => true, 
                'post_status'           => 'publish', 
                'ignore_sticky_posts'   => true,
                'cat'              => $category,
                'tax_query' => $tax_query,
		//added following three to sort by update time - JM
		'meta_key' => 'updatetime',
		'orderby' => 'meta_value_num',
		'order' => 'DESC'  
          ) ) );
    }


    function get_images( $post_id ){

        preg_match_all('/\[gallery\sids=\"([^\]]+)\".*\]/', get_the_content( $post_id ), $gallery_shortcode ) ;

        $attachment_ids = $gallery_shortcode[1][0];
        
        $images = array();
        if( $attachment_ids ) {
            $attachments = explode(',', $attachment_ids);
            foreach ($attachments as $img_id ) {
                $img = get_post( $img_id );
                $images[] = array(
                        wp_get_attachment_url( $img_id ),
                        $img->post_title
                    );
            }
        } else {
            $query_images_args = array(
                'post_type' => 'attachment', 'post_mime_type' =>'image', 'post_status' => 'inherit', 'posts_per_page' => -1, 'post_parent' => $post_id
            );

            $query_images = get_children( $query_images_args );

            if( ! empty($query_images) ) {
                foreach ( $query_images as $image) {
                    $images[]= array( 
                        wp_get_attachment_url( $image->ID ),
                        $image->post_title
                    );
                }
            }
        }

        
        return $images;
    }
}

class dw_focus_recents_news_Widget extends dw_focus_recents_posts_Widget {
     function __construct() {
        $widget_ops = array('classname' => 'dw_focus_recents_posts latest-news', 'description' => __( 'Display latest news with many settings', 'dw_focus' ) );
        parent::__construct('dw_focus_recent_news', __( 'DW Focus: Recent News', 'dw_focus' ), $widget_ops);
        $this->alt_option_name = 'widget_recent_entries';

        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

}

class dw_focus_popular_news_Widget extends dw_focus_recents_posts_Widget {
    function __construct() {
        $widget_ops = array('classname' => 'dw_focus_popular_posts latest-news', 'description' => __( 'Display most viewed news with many settings', 'dw_focus' ) );
        parent::__construct('dw_focus_popular_news', __( 'DW Focus: Popular News', 'dw_focus' ), $widget_ops);
        $this->alt_option_name = 'widget_popular_entries';

        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    function query($instance){
        
        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
            $number = 10;

        $category = $instance['category'];
        if( is_category() && dw_focus_sidebar_has_widget( 'dw_focus_category_sidebar', $this->id ) ){

            $category = get_query_var( 'cat' );
        }
        $post_with_views = new WP_Query( 
            array( 
                'posts_per_page'    => $number, 
                'no_found_rows'         => true, 
                'post_status'           => 'publish', 
                'ignore_sticky_posts'   => true,
                'cat'              => $category,
                'orderby'   =>  "meta_value_num",
                'meta_key'  =>  '_views',
                'order' =>  'DESC',
            )
        );
        if( $post_with_views->post_count < $number ) {
            $post_dont_with_views = new WP_Query( array( 
                    'posts_per_page'    => $number - $post_with_views->post_count , 
                    'no_found_rows'         => true, 
                    'post_status'           => 'publish', 
                    'ignore_sticky_posts'   => true,
                    'cat'              => $category,
                    'meta_query'    =>  array(
                            array(
                                'key'  =>  '_views',
                                'compare'  =>  'NOT EXISTS',
                            )
                        )

            ) );
            $post_with_views->posts = array_merge($post_with_views->posts,$post_dont_with_views->posts);
            $post_with_views->post_count += $post_dont_with_views->post_count;
        }

        return $post_with_views;
    }

}

class dw_focus_featured_news_Widget extends dw_focus_recents_posts_Widget {
    //JM - added function widget to differentiate the featured news widget from recent news ones - used for home page top stories
    function widget($args, $instance) {

        $cache = wp_cache_get('widget_dw_focus_recent_posts', 'widget');

        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

        ob_start();
        extract($args);

        $title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '', $instance, $this->id_base);
        $thumbnail_size = isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : 'thumbnail';

        echo $before_widget;
	echo '<div id="top-stories">';
	
        $r = $this->query($instance);
        global $more;
        if ( $r->have_posts() ) :
            $i = 0; //Dectect first post
                if( $i == 0 && isset($instance['thumbnail']) && $instance['thumbnail'] ){ 

                $r->the_post(); $i++; 
                $more=0;
                $class = 'first';
	        $img_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $thumbnail_size );
	        $imgwidth = $img_info[1];
		$imgheight = $img_info[2];
		$relatedlinks = get_post_meta( get_the_ID(), 'journal_related_links');
                
		if( has_post_thumbnail( get_the_ID() ) ) {
                    $class .= ' has-thumbnail';                    	
	                if($imgwidth >= $imgheight ) {
        	        	if($imgwidth > 500) {
                	                $newimgwidth = 61; //make percentage so it works with responsive
	               		} else {
					$newimgwidth = ($imgwidth/820)*100; //gets inital percentage to carry throughout the screen sizes
				}
			} else {
				//if($imgheight > 400) {
				//	$newimgwidth = (((400*$imgwidth)/$imgheight)/820)*100;
				//} else {
					$newimgwidth = ($imgwidth/820)*100;
					if($newimgwidth > 61) {
						$newimgwidth = 61;
					}
				//}
			}  ?>
		   <article <?php post_class($class); ?> style="width: <?php echo $newimgwidth; ?>%;">
		           <div class="entry-thumbnail">
				   <?php if( $instance['post-format'] == 'gallery' ) { ?>
                    		   <?php } else { ?>
                    			<a href="<?php the_permalink() ?>">
                        			<?php the_post_thumbnail($thumbnail_size); ?>
                    			</a>
                    		   <?php } ?>
                	   </div>
			   <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                		<?php if( isset( $instance['meta'] ) && $instance['meta'] ) { ?>
                		<p class="entry-meta">
                			<?php if( isset( $instance['date'] ) && $instance['date'] ) {
                        			echo dw_focus_time_stamp( get_the_date() );
                    			}?>
                		</p>
                		<?php } ?>
		                <?php if( isset($instance['post_excerpt']) && $instance['post_excerpt'] ) {  ?>
                			<div class="entry-content">
                        			<?php the_excerpt(); ?>
						<?php if(! empty($relatedlinks)) { ?>	
							<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
                        			<?php } ?>
                			</div>
                		<?php } ?>
            	</article>
		<?php } else { ?>
       		<!-- should not be used, only so it doesn't look absolutely hideous if someone forgets a picture -->
		<article <?php post_class($class); ?> style="float: none; margin: 20px; padding: 5px;">
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php if( isset( $instance['meta'] ) && $instance['meta'] ) { ?>
                <p class="entry-meta">
                <?php 
                    if( isset( $instance['date'] ) && $instance['date'] ) { 
                        echo dw_focus_time_stamp( get_the_date() );
                    }
                ?>
                </p>
                <?php } ?>
                
                <?php if( isset($instance['post_excerpt']) 
                                && $instance['post_excerpt'] ) {  ?>
                <div class="entry-content">
			<?php the_excerpt(); ?>
			<?php if(! empty($relatedlinks)) { ?>	
				<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
			<?php } ?>
		</div>
                <?php } ?>
            	</article>
		<?php } ?>

	    <div id="top-stories-list">
		<?php if ( !empty( $title ) ) {
		//	echo $before_title . $title . $after_title;
		} ?>
		<hr class="topstories-separator">
            <?php } ?>
            <?php
                $i = 0;
                while ($r->have_posts()) : $r->the_post(); 
                $class = 'item';
                $post_id = get_the_ID();
                $relatedlinks = get_post_meta( $post_id, 'journal_related_links' );
	    ?>
            <article <?php post_class($class); ?> >
                
                <h2 class="entry-title"><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></h2>   
            	<?php if( isset($instance['post_excerpt']) && $instance['post_excerpt'] ) {  ?>
                	<div class="entry-content">
                        	<?php the_excerpt(); ?>
                        	<?php if(! empty($relatedlinks)) { ?>
                                	<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
                        	<?php } ?>
                	</div>
                <?php } ?>

	    </article>

            <?php endwhile; ?>
        <?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();
        else:
            get_template_part('no-results');
        endif;
	?>
	</div><!-- top-stories-list -->
	</div><!-- top-stories-->
	<div class="clear"></div>
	
	<?php
        echo $after_widget; ?>
	<div class="clear"></div>
	<?php
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_recent_posts', $cache, 'widget');
    }
    function __construct() {
        $widget_ops = array('classname' => 'dw_focus_featured_posts latest-news', 'description' => __( 'Display featured news for home page, uses sticky posts - styled by Jolie', 'dw_focus' ) );
        parent::__construct('dw_focus_featured_news', __( 'DW Focus: Featured News', 'dw_focus' ), $widget_ops);
        $this->alt_option_name = 'widget_popular_entries';

        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    function query($instance){


        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
            $number = 10;
        $sticky_posts = get_option('sticky_posts');

        $category = $instance['category'];
        if( is_category() && dw_focus_sidebar_has_widget( 'dw_focus_category_sidebar', $this->id ) ){
            $category = get_query_var( 'cat' );
        }
        $posts = new WP_Query( 
            array( 
                'post__in'  =>  $sticky_posts,
                'posts_per_page'    => $number, 
                'no_found_rows'         => true, 
                'post_status'           => 'publish', 
                'ignore_sticky_posts'   => true,
                'cat'                   => $category
            )
        );
        return $posts;
    }
}
class dw_focus_featured_sports_Widget extends dw_focus_recents_posts_Widget {
    //JM - added function widget to differentiate the featured sports widget from recent news ones - used for sports page top stories
    function widget($args, $instance) {

        $cache = wp_cache_get('widget_dw_focus_recent_posts', 'widget');

        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

        ob_start();
        extract($args);

        $title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '', $instance, $this->id_base);
        $thumbnail_size = isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : 'thumbnail';

        echo $before_widget;
	echo '<div id="top-stories">';
	
        $r = $this->query($instance);
        global $more;
        if ( $r->have_posts() ) :
            $i = 0; //Dectect first post
                if( $i == 0 && isset($instance['thumbnail']) && $instance['thumbnail'] ){ 

                $r->the_post(); $i++; 
                $more=0;
                $class = 'first';
	        $img_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $thumbnail_size );
	        $imgwidth = $img_info[1];
		$imgheight = $img_info[2];
		$relatedlinks = get_post_meta( get_the_ID(), 'journal_related_links');
                
		if( has_post_thumbnail( get_the_ID() ) ) {
                    $class .= ' has-thumbnail';                    	
	                if($imgwidth >= $imgheight ) {
        	        	if($imgwidth > 500) {
                	                $newimgwidth = 61; //make percentage so it works with responsive
	               		} else {
					$newimgwidth = ($imgwidth/820)*100; //gets inital percentage to carry throughout the screen sizes
				}
			} else {
				//if($imgheight > 400) {
				//	$newimgwidth = (((400*$imgwidth)/$imgheight)/820)*100;
				//} else {
					$newimgwidth = ($imgwidth/820)*100;
					if($newimgwidth > 61) {
						$newimgwidth = 61;
					}
				//}
			}  ?>
		   <article <?php post_class($class); ?> style="width: <?php echo $newimgwidth; ?>%;">
		           <div class="entry-thumbnail">
				   <?php if( $instance['post-format'] == 'gallery' ) { ?>
                    		   <?php } else { ?>
                    			<a href="<?php the_permalink() ?>">
                        			<?php the_post_thumbnail($thumbnail_size); ?>
                    			</a>
                    		   <?php } ?>
                	   </div>
			   <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                		<?php if( isset( $instance['meta'] ) && $instance['meta'] ) { ?>
                		<p class="entry-meta">
                			<?php if( isset( $instance['date'] ) && $instance['date'] ) {
                        			echo dw_focus_time_stamp( get_the_date() );
                    			}?>
                		</p>
                		<?php } ?>
		                <?php if( isset($instance['post_excerpt']) && $instance['post_excerpt'] ) {  ?>
                			<div class="entry-content">
                        			<?php the_excerpt(); ?>
						<?php if(! empty($relatedlinks)) { ?>	
							<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
                        			<?php } ?>
                			</div>
                		<?php } ?>
            	</article>
		<?php } else { ?>
       		<!-- should not be used, only so it doesn't look absolutely hideous if someone forgets a picture -->
		<article <?php post_class($class); ?> style="float: none; margin: 20px; padding: 5px;">
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php if( isset( $instance['meta'] ) && $instance['meta'] ) { ?>
                <p class="entry-meta">
                <?php 
                    if( isset( $instance['date'] ) && $instance['date'] ) { 
                        echo dw_focus_time_stamp( get_the_date() );
                    }
                ?>
                </p>
                <?php } ?>
                
                <?php if( isset($instance['post_excerpt']) 
                                && $instance['post_excerpt'] ) {  ?>
                <div class="entry-content">
			<?php the_excerpt(); ?>
			<?php if(! empty($relatedlinks)) { ?>	
				<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
			<?php } ?>
		</div>
                <?php } ?>
            	</article>
		<?php } ?>

	    <div id="top-stories-list">
		<?php if ( !empty( $title ) ) {
		//	echo $before_title . $title . $after_title;
		} ?>
		<hr class="topstories-separator">
            <?php } ?>
            <?php
                $i = 0;
                while ($r->have_posts()) : $r->the_post(); 
                $class = 'item';
                $post_id = get_the_ID();
                $relatedlinks = get_post_meta( $post_id, 'journal_related_links' );
	    ?>
            <article <?php post_class($class); ?> >
                
                <h2 class="entry-title"><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></h2>   
            	<?php if( isset($instance['post_excerpt']) && $instance['post_excerpt'] ) {  ?>
                	<div class="entry-content">
                        	<?php the_excerpt(); ?>
                        	<?php if(! empty($relatedlinks)) { ?>
                                	<p><?php do_action( 'journal_related_links', 'horizontal' ); ?></p>
                        	<?php } ?>
                	</div>
                <?php } ?>

	    </article>

            <?php endwhile; ?>
        <?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();
        else:
            get_template_part('no-results');
        endif;
	?>
	</div><!-- top-stories-list -->
	</div><!-- top-stories-->
	<div class="clear"></div>
	
	<?php
        echo $after_widget; ?>
	<div class="clear"></div>
	<?php
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_recent_posts', $cache, 'widget');
    }
    function __construct() {
        $widget_ops = array('classname' => 'dw_focus_featured_posts latest-news', 'description' => __( 'Display featured news for sports page - styled by Jolie', 'dw_focus' ) );
        parent::__construct('dw_focus_featured_sports_news', __( 'DW Focus: Featured Sports', 'dw_focus' ), $widget_ops);
        $this->alt_option_name = 'widget_top_sports_entries';

        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }
}



add_action( 'widgets_init', create_function( '', "register_widget('dw_focus_recents_news_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('dw_focus_popular_news_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('dw_focus_featured_news_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('dw_focus_featured_sports_Widget');" ) );

?>
