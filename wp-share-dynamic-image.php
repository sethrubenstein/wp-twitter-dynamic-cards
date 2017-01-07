<?php
/*
Plugin Name: WP Share Dynamic Image
Plugin URI: https://github.com/sethrubenstein/wp-share-dynamic-image
Description: Lets you share a post while changing the image, description for a shared card (Twitter or Facebook)
Version: 1.0
Author: Seth Rubenstein
Author URI: http://sethrubenstein.info
*/

// {site_url}/share/{post_id}/{image_id}

function wpsdi_rewrite_activation(){
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpsdi_rewrite_activation' );

function wpsdi_rewrite_add_var( $vars ){
    $vars[] = 'postid';
    $vars[] = 'imageid';
    return $vars;
}
add_filter( 'query_vars', 'wpsdi_rewrite_add_var' );

function wpsdi_rewrite_rule() {
    add_rewrite_rule('^share/([^/]*)/([^/]*)/?','index.php?postid=$matches[1]&imageid=$matches[2]','top');
}
add_action('init', 'wpsdi_rewrite_rule', 10, 0);

function wpsdi_get_share_url($postID, $imageID) {
    return get_bloginfo( 'url' ).'/share/'.$postID.'/'.$imageID;
}

function wpsdi_get_site_twitter_username() {
    $username = '';
    if ( get_option( 'twitter_site' ) ) {
        $username = get_option( 'twitter_site' );
    }
    return apply_filters( 'wpsdi_twitter_username', $username );
}

function wpsdi_rewrite_catch() {
    if( get_query_var( 'imageid' ) && get_query_var( 'postid' ) ) {
        $site_name = apply_filters( 'wpsdi_site_name', get_bloginfo( 'name' ) );
        $twitter_username = wpsdi_get_site_twitter_username();
        $twitter_creator = apply_filters( 'wpsdi_twitter_creator', '' );

        $postID = get_query_var( 'postid' );
        $post_OBJ = get_post( $postID );
        $image_size = apply_filters( 'wpsdi_image_size', 'large' );
        $image_OBJ = wp_get_attachment_image_src( get_query_var( 'imageid' ), $image_size );
        $title = $post_OBJ->post_title;
        $description = $post_OBJ->post_excerpt;
        ?>
        <html lang="en-US">
        <head>
        <meta charset="UTF-8">
        <!-- Misc -->
        <meta property="og:locale" content="en_US" />
        <meta property="og:site_name" content="<?php echo $site_name;?>" />
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@<?php echo $twitter_username;?>">
        <meta property="og:type" content="article" />
        <!-- Facebook -->
        <meta property="og:image" content="<?php echo $image_OBJ[0];?>" />
        <meta property="og:image:width" content="<?php echo $image_OBJ[1];?>" />
        <meta property="og:image:height" content="<?php echo $image_OBJ[2];?>" />
        <meta property="og:title" content="<?php echo $title;?>" />
        <meta property="og:description" content="<?php echo $description;?>" />
        <meta property="og:url" content="<?php echo get_permalink($postID);?>" />
        <meta property="article:published_time" content="<?php echo get_the_date( 'Y-m-d', $postID );?>" />
        <!-- Twitter -->
        <?php if ( !empty($twitter_creator) ) {
            echo '<meta name="twitter:creator" content="@'.$twitter_creator.'">';
        }?>
        <meta name="twitter:title" content="<?php echo $title;?>">
        <meta name="twitter:description" content="<?php echo $description;?>">
        <meta name="twitter:image" content="<?php echo $image_OBJ[0];?>">
        </head>
        <body>
        <!-- This is generated by Seth Rubenstein's WP Share Dynamic Image plugin. https://github.com/sethrubenstein/wp-share-dynamic-image -->
        </body>
        </html>
        <?php
        exit();
    }
}
add_action( 'template_redirect', 'wpsdi_rewrite_catch' );