<body <?php body_class(); ?>>
  <div class="category_dashboard">
    <div class="dash_title">
      <h1>Restrict category post</h1>
    </div>
  <?php

global $wp_roles;

$key = get_option('sample_license_key');

$roles = array(

    "Administrator",

    "Editor",

    "Author",

    "Contributor",

    "Subscriber"
);

?>
      <form method="POST" action="">

          <div class="category_main_box">

                      <?php

foreach ($roles as $role)

{ ?>

                             <div class="category_boxes">

                              <h2><?php echo esc_html($role); ?></h2>

                              <?php

    $args = array(
        'role' => $role
    );

    $users = get_users($args);

    foreach ($users as $user)

    {
?>

    <input type="hidden" name="user_data[]" value="<?php echo $user->ID; ?>"> 

<?php } 

    $orderby = 'ID';

    $order = 'asc';

    $hide_empty = false;

   
        $cat_args = array(

            'orderby' => $orderby,

            'order' => $order,

            'hide_empty' => $hide_empty,

            'number' => 3,

            'parent' => 0

        );


    $orderby = 'name';

    $order = 'asc';

    $hide_empty = false;

    $user_args = array(

        'orderby' => $orderby,

        'order' => $order,

        'hide_empty' => $hide_empty,

    );

    $product_categories = get_terms('category', $cat_args);

    if (is_wp_error($product_categories))

    $product_categories = array();

    $category = array(); ?>

<div class="check_list">

                   <?php $user_role_name_none = 'ckecklist_' . $role;
    $args = array(

        'role' => $role

    );

    $users = get_users($args);

    if (count($users) == "0")

    {

        echo '<p class="user_note">No users available under this privileges.please create one.</p><br>'; ?>

                         <input type="checkbox" class="selectall_<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>" value="0"  disabled>

                         None  
                <?php

    }

    else

    { ?>

<input type="checkbox" class="selectall_<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>" value="0"  <?php if (isset($_POST[$user_role_name_none])) echo "checked='checked'"; ?>>

          None     

              <?php

    } ?>         

 </div>
          <?php

    foreach ($product_categories as $product)

    {
        $category[] = array(

            'slug' => $product->slug,
            'name' => $product->name

        );

?>

                        <div class="check_list">
                            <?php

        $users = get_users($args);
        foreach ($users as $user)

        {

            $cat_id = get_user_meta($user->ID, 'category_key', true);
            $var = (in_array($product->term_taxonomy_id, $cat_id));
        }

        if (count($users) == "0")

        { ?>

                                 <input type="checkbox" class="justone<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>[]" value="<?php echo $product->term_taxonomy_id; ?>" disabled>
                      <?php

        }

        else

        { ?>

<input type="checkbox" class="justone<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>[]" value="<?php echo $product->term_taxonomy_id; ?>" <?php echo ($var) ? "checked" : ""; ?>>

                    <?php

        }

?>

          <?php echo esc_html($product->name); ?>

          <?php $termchildren = get_term_children($product->term_taxonomy_id, 'category');



        foreach ($termchildren as $child)

        { ?>

                    <?php

            $users = get_users($args);



            foreach ($users as $user)

            {
                $cat_id = get_user_meta($user->ID, 'category_key', true);
                $var2 = (in_array($child, $cat_id));

            }



?>
<div class="child_cat">

  <?php
            if (count($users) == "0")

            { ?>
          <input type="checkbox" class="justone<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>[]" value="<?php echo $child; ?>" disabled>
   <?php

            }

            else

            { ?>
      <input type="checkbox" class="justone<?php echo $role; ?>" name="ckecklist_<?php echo $role; ?>[]" value="<?php echo $child; ?>" <?php echo ($var2) ? "checked" : ""; ?>>

  <?php

            }

?>

                    <?php echo esc_html(get_cat_name($child)); ?>
                    </div>
             <?php

        }
?>

      </div>
        <?php

    } //end foreach

     ?>
       </div>
     <?php

} ?>

</div>

     <input type="submit" name="submit" class="up_cat" value="Update">
          </form>
</div>



<script type="text/javascript"> 
 jQuery(document).ready(function(){

  var books = JSON.parse( '<?php echo json_encode($roles); ?>' );

jQuery.each(books, function(key,value){

  jQuery('input.selectall_'+value).on('change', function() {

    if(jQuery(this).prop('checked')){

      jQuery(this).parent().siblings().find('input').prop('checked', false);

      jQuery(this).parent().siblings().find('input').prop('disabled', true);  

    } else {

      jQuery(this).parent().siblings().find('input').prop('disabled', false); 

    }

  });

    jQuery('input.justone'+value).on('change', function() {

   if(jQuery(this).prop('checked')){

      jQuery(this).siblings().find('input').prop('checked', true);

    } else {

        jQuery(this).siblings().find('input').prop('checked', false);

    }  

     if(!jQuery(this).parent().find('input').prop('checked')){
        jQuery(this).parent().parent().find('input:first').prop('checked', false);
    } 


if(jQuery(this).prop('checked')){

  var c_total = jQuery(this).parent().parent().children('.child_cat').length;

  var c_check = jQuery(this).parent().parent().children('.child_cat').find('input:checked').length;
       if(c_total == c_check ) {

jQuery(this).parent().parent().children('input').prop('checked', true);
       }
}

  });

});

var countChecked = function() {
  jQuery('.category_boxes').each(function(){
 var n = jQuery(this).find('input[class^="justone"]:checked').length;

  if(n > 0){

       jQuery(this).find('input[class^="selectall_"]').prop('checked', false);

    }

if(n <= 0){
  jQuery(this).find('input[class^="selectall_"]').prop('checked', true);

        jQuery(this).find('input[class^="justone"]').prop('checked', false);

    jQuery(this).find('input[class^="justone"]').prop('disabled', true);  

    }

  })

};

countChecked();

 });

</script>

</body>

