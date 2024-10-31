<?php
/**
 * Plugin Name: Role wise category post
   Description: This plugin allows admin to set custom privillages for users. It will restrict the post based on selected category. Based on those privillages end-user will be able to see posts accordingly.
   To display list of categories using following shortcode:
   [selected_category_list].
   Version:1
   Author: Innowyn Business Solutions
   Author URI: http://innowyn.com/

 */

function rwcp_category_post_activate() {
  rwcp_category_post_user_role_menu();
  
}
register_activation_hook( __FILE__, 'rwcp_category_post_activate' );


function rwcp_category_post_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rwcp_category_post_deactivate' );


// add menu in wordpress dashboard
function rwcp_category_post_user_role_menu(){
add_menu_page('Restrict Category Post By Admin','Restrict Category Post By Admin','manage_options',"wp_restrict_category_post_user_role",'rwcp_category_post_user_roles','dashicons-category');

}
add_action('admin_menu','rwcp_category_post_user_role_menu');


//callable function for the admin menu
function rwcp_category_post_user_roles(){ 
define( 'RWCP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
include( RWCP_PLUGIN_PATH . 'category-dashboard.php');
} 

function rwcp_category_css_js_files(){

       wp_enqueue_style('category-dashboard', plugin_dir_url( __FILE__ ) . 'css/category-dashboard.css' );
    }
add_action( 'admin_enqueue_scripts', 'rwcp_category_css_js_files' );



    
function rwcp_category_css_js_files_front(){
       wp_enqueue_style('category-dashboard', plugin_dir_url( __FILE__ ) . 'css/front.css' );
    }
add_action( 'wp_enqueue_scripts', 'rwcp_category_css_js_files_front' );


function rwcp_store_assign_category_data() {

if(isset($_POST['submit'])){

$id = array_map('intval',$_POST['user_data']);
 
 global $wp_roles;
 $roles = $wp_roles->get_names();
 if($roles !== ""){
 foreach($roles as $role) {

 $category_arr = array(); 

 $user_role_name = 'ckecklist_'.$role;

 $category_id = array_map('intval', $_POST[$user_role_name]);

           if($category_id !== ""){
           foreach ($category_id as $cate_id){

                       $category_arr[] = $cate_id;

    }

}

     foreach ($id as $user_id){
    $user_meta=get_userdata($user_id);

    $user_roles=$user_meta->roles;

    $role_name = strtolower($role);

     if (in_array($role_name, $user_roles)){

      update_user_meta( $user_id, 'category_key', $category_arr );
    }

    }

 }
}

  }

}

add_action( 'init', 'rwcp_store_assign_category_data' );



function rwcp_display_selected_category( $query ) {
$id = get_current_user_id();
$cat_id = get_user_meta( $id, 'category_key' ,true );
$cat_ids = implode(",",$cat_id);
if ( ! is_admin() && $query->is_main_query() ) {
    $query->set( 'cat', $cat_ids );
    $ids = get_posts( array(
    'post_type' => 'post',
    'pages_per_post' => -1,
) );

$ids = wp_list_pluck( $ids, 'ID' );

if ($cat_ids == ""){
      $query->set('post__not_in', $ids);

  }

    }
}

add_action( 'pre_get_posts', 'rwcp_display_selected_category' );



function rwcp_category_exclude_posts_recent() {
$id = get_current_user_id();
$cat_id = get_user_meta( $id, 'category_key' ,true );
$cat_ids = implode(",",$cat_id);
$exclude_post =  get_posts(array(
    'fields' => 'ids', // Only get post IDs
    'posts_per_page'  => -1
));

  if ($cat_ids != ""){
    
   $args = array(  'cat' => $cat_ids );
   return $args;
   } else {

     $args = array(  'post__not_in' => $exclude_post );
     return $args;
  }

}

add_filter( 'widget_posts_args', 'rwcp_category_exclude_posts_recent');



//Hide categories from WordPress category widget
function rwcp_exclude_widget_categories($args){
  $id = get_current_user_id();
  $cat_id = get_user_meta( $id, 'category_key' ,true );
  $cat_ids = implode(",",$cat_id);
      $include = $cat_ids;
      $exclude_cat = get_all_category_ids();
     if ($include != ""){
       $args["include"] = $include;
       return $args;
} else{

    $args["exclude"] = $exclude_cat;
    return $args;
  }
}

add_filter("widget_categories_args","rwcp_exclude_widget_categories");


//Shortcode for the display only selected category.
function rwcp_selected_cateory_front(){

    $id = get_current_user_id();
    $cat_id = get_user_meta( $id, 'category_key' ,true );
    $cat_data =[];

     $category_information_main=get_categories(

                    array( 'include' => $cat_id,)
                  );

    foreach ($cat_id as  $cat) {

      if(!in_array($cat,$cat_data)){

      $category_link = get_category_link($cat);
      $category_information = get_category($cat);
      if($category_information->parent == 0 ){

        $categories=get_categories(
         array( 'parent' => $category_information->term_id )
        );

if(in_array($category_information,$category_information_main)){

   $category_list  .= '<div class="category_list"><a href='." $category_link".'><div class="title_cat">'.(esc_html($category_information->name)).'</a></div>';

}
   array_push($cat_data,$cat);

        foreach ($categories as $sub_cat) {
          $category_sub_link = get_category_link($sub_cat);

          $category_list  .= '<div class="category_list_sub"><a href='." $category_sub_link".'>'.($sub_cat->name).'</a></div>';

          array_push($cat_data,$sub_cat->term_id);
        } 
      $category_list .= '</div>';         

    } else {

      if ($category_information->count > 0) {

           $parent_cat = get_term( $category_information->parent, 'category' );

           if(!in_array($parent_cat->term_id,$cat_data)){

              $parent_cat_link = get_category_link($parent_cat->term_id);

                $category_list  .= '<div class="category_list">

                  <a href='." $parent_cat_link".'>
                    <div class="title_cat">'.($parent_cat->name).'
                    </div>
                  </a>';

                 $level1cat=get_categories(
                    array( 'parent' => $parent_cat->term_id,'include' => $cat_id,)

                  );

                 array_push($cat_data,$parent_cat->term_id);

              foreach ($level1cat as $sub_cat) {
                  $category_sub_link = get_category_link($sub_cat->term_id);
                  $category_list  .= '<div class="category_list_sub">
                  <a href='." $category_sub_link".'>'.($sub_cat->name).'</div></a>';
                  array_push($cat_data,$sub_cat->term_id);
              } 
          $category_list .= '</div>';
        }
   }

}

    }

}

return $category_list;

}

add_shortcode('selected_category_list', 'rwcp_selected_cateory_front');





/**/
function rwcp_category_insert_default_user() {

$user_data = array(
'ID' => '',
'user_pass' => wp_generate_password(),
'user_login' => 'dummy',
'user_nicename' => 'Dummy',
'user_url' => '',
'user_email' => 'dummy@example.com',
'display_name' => 'Dummy',
'nickname' => 'dummy',
'first_name' => 'Dummy',
'user_registered' => '2010-05-15 05:55:55',
'role' => get_option('default_role') // Use default role or another role, e.g. 'editor'
);

$user_id = wp_insert_user( $user_data );
}
add_action( 'admin_init', 'rwcp_category_insert_default_user' );