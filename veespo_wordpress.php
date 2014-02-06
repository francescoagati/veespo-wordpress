<?php

/*
Plugin Name: Veespo Widget
Plugin URI: http://www.veespo.com
Description: Veespo
Author: C. Veespo
Version: 1.0
Author URI: http://www.veespo.com
*/

function veespo_get_tokens($category) {

  $api     =  "http://production.veespo.com";
  $apikey  =  get_option('veespo_wp_partner_api_key');
  $partner =  get_option('veespo_wp_partner');
  $user    =  get_current_user_id();
  

  
  $user_veespo    =  "wordpress_veespo_".$user;
 
  if(count(get_user_meta($user, "veespo_tokens")) == 0) {
     
    $url =  $api."/v1/auth/partner/".$partner."/user-tokens?api_key=".$apikey."&categories=all&user=".$user;
    $response = json_decode(file_get_contents($url))->data;
    update_user_meta( $user, "veespo_tokens", $response);
  }
  
  $tokens = get_user_meta($user, "veespo_tokens");
  
  if (!isset($tokens[0]->{$category})) {
    $url =  $api."/v1/auth/partner/".$partner."/user-tokens?api_key=".$apikey."&categories=all&user=".$user;
    $response = json_decode(file_get_contents($url))->data;
    update_user_meta( $user, "veespo_tokens", $response);
    $tokens = get_user_meta($user, "veespo_tokens");
  }
  
  return $tokens[0]->{$category};
}
    
  
function veespo_inject_widget($options) { 

  $partner =  get_option('veespo_wp_partner');
  $element = "tgt-".$options['target'].time().rand();   
  $target  = $options['target'];
  $title   = $options['title'];
  $category = $options['category'];
  $token_user   = veespo_get_tokens($category); 
  $description = $options['description'];

  $lang = $options['lang'];
  $key1 = $options['key1'];
  $key2 = $options['key2'];
  $key3 = $options['key3'];
  $key4 = $options['key4'];
  $key5 = $options['key5'];
  $version = $options['version'];
  
  $html = "<span id=\"$element\"></span>"; 
  $html.= "<script>"; 
  $html.= "   var params = {";
  $html.= "     title:'$title',";
  $html.= "     target:'$target',";
  $html.= "     group:'group-vsite',";
  $html.= "     lang:'$lang',";

  if ($key1 != "") $html.= "     key1:'$key1',";
  if ($key2 != "") $html.= "     key1:'$key2',";
  if ($key3 != "") $html.= "     key1:'$key3',";
  if ($key4 != "") $html.= "     key1:'$key4',";
  if ($key5 != "") $html.= "     key1:'$key5',";
  if ($version != "") $html.= "     key1:'$version',";
  
  if (strlen($token_user) == 0) {
    $html.= "token_info:{partner:'$partner',category:'$category',anonymous:'true'},";
  } else {
    $html.= "     token:'$token_user',";
  }
  $html.= "     target_info: {";
  $html.= "       local_id:'$target',";
  $html.= "       desc1:'$title',";
  $html.= "       desc2:'$description',";
  $html.= "     },";     
     
  
  $html.= "     enviroment: {";
  $html.= "       apiUrl:'http://production.veespo.com'";
  $html.= "     },";      
  $html.= "     callback: function(response) {}";
  $html.= "   };";
  $html.= "   window._veespo_push = window._veespo_push || [];";
  $html.= "   window._veespo_push.push(['widget.button-modal','$element',params]);";    
  $html.= "</script>";
   
  return $html;
}



register_activation_hook(__FILE__,'veespo_wp_activate');
register_deactivation_hook(__FILE__,'veespo_wp_deactivate');

function veespo_wp_activate() {
  add_option('veespo_wp_partner','');
  add_option('veespo_wp_partner_api_key','');
}

function veespo_wp_deactivate() {
  delete_option('veespo_wp_partner');
  delete_option('veespo_wp_partner_api_key');
}



// Shortcode

function show_veespo_button($atts) {
  
  $options = shortcode_atts(array(
          'category' => '',
          'target' => '',
          'title' => '',
          'description' => '',
          'use_post' => '',
          'lang' => 'it',
          'key1' => '',
          'key2' => '',
          'key3' => '',
          'key4' => '',
          'key5' => '',
          'version' => '',
  ), $atts);
  
  
  if ($options['use_post'] == "true") {
    $title = get_the_title();
    $options['target'] = get_post_type()."-".get_the_id();
    $options['title'] = $title;
    $options['description'] = $title;
  } 
  

  
  return veespo_inject_widget($options);
	
}

add_shortcode('veespo-button', 'show_veespo_button');



function theme_name_scripts() {
   wp_enqueue_script( 'script-name', "http://cdn.veespo.com/static/javascripts/widget.js", array(), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );


// Admin Page

add_action('admin_menu', 'veespo_wp_menu');

function veespo_wp_menu() {
  add_options_page('Veespo Options', 'Veespo options', 'manage_options', 'veespo-options', 'veespo_wp_options');
}

function veespo_wp_options() {

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

?>

<div class="wrap">
    <div id="icon-plugins" class="icon32"></div>
    <h3>Veespo Wordpress</h3>
    
    <div id="icon-options-general" class="icon32"></div><h2>Options</h2>

    <form method="post" action="options.php">
      <?php wp_nonce_field('update-options'); ?>

      <h3>API Key</h3>
      
      <table class="form-table">
      
        <tr valign="top">
        <th scope="row">Partner Name</th> 
        <td><fieldset><legend class="screen-reader-text"><span>Partner Name</span></legend> 
        <input name="veespo_wp_partner" type="text" id="veespo_wp_partner" value="<?php echo get_option('veespo_wp_partner'); ?>" />
        </fieldset></td> 
        </tr>

        <tr valign="top">
        <th scope="row">Partner Api Client</th> 
        <td><fieldset><legend class="screen-reader-text"><span>Partner Api Client</span></legend> 
        <input name="veespo_wp_partner_api_key" type="text" id="veespo_wp_partner_api_key" value="<?php echo get_option('veespo_wp_partner_api_key'); ?>" />
        </fieldset></td> 
        </tr>	  
        
      </table>
      
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options" value="veespo_wp_partner,veespo_wp_partner_api_key" />
      
      <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
</div>

<?php
}
?>
