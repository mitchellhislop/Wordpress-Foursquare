<?php
/*
 * Plugin Name: Foursquare Todo Adder
 * Plugin URI: http://www.smcpros.com
 * Version: 1.0
 * Author: Mitchell Hislop and Tim Barsness of <a href="http://www.smcpros.com">SMCpros</a>
 * Description: This plugin allows you to add a button to a post that will add a todo to Foursquare for a user
 */


if (!class_exists("FoursquareTodoAdder"))
{	
	class FoursquareTodoAdder
	{	
		var $admin_options_name="foursquare_todo_adder_option";
		function FoursquareTodoAdder()
		{
			
		}
		
		function init()
		{
			$this->get_admin_options();
		}
		
		function get_admin_options()
		{
			$foursquare_admin_options=array('button_color'=>'light');
			$foursq_options=get_option($this->admin_options_name);
			if(!empty($foursq_options))
			{
				foreach ($foursq_options as $key => $option)
				{
					$foursquare_admin_options[$key] = $option;
				}
			}
			update_option($this->admin_options_name, $foursquare_admin_options);
			return $foursquare_admin_options;
		}
		
		function print_admin_page()
		{
			$dev_options=$this->get_admin_options();
			
			if(isset($_POST['update_foursquare_setting']))
			{
				if(isset($_POST['foursquare_button_color']))
				{
					$dev_options['button_color']=$_POST['foursquare_button_color'];
					
				}
			
			update_option($this->admin_options_name, $dev_options);
			?>
			<div class="updated"><p><strong>Settings Updated</strong></p></div>
			<? } ?>
			<div class=wrap>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"];?>">
			<h2>Foursquare</h2>
			<h3>Choose the button color you would like:</h3>
			<p><label for="foursquare_button_color_light"><input type="radio" id="foursquare_button_color_light" name="foursquare_button_color" value="light" <?php if ($dev_options['button_color'] == "light") { _e('checked="checked"', "FoursquareTodoAdder"); }?> /> <img src="http://foursquare.com/img/buttons/add_to_foursquare_light.png"</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="foursquare_button_color_dark"><input type="radio" id="foursquare_button_color_dark" name="foursquare_button_color" value="dark" <?php if ($dev_options['button_color'] == "dark") { _e('checked="checked"', "FoursquareTodoAdder"); }?>/> <img src="http://foursquare.com/img/buttons/add_to_foursquare_dark.png"</label></p>
			<div class="submit">
			<input type="submit" name="update_foursquare_setting" value="Update Settings" />
			</div>
			</form>
			</div>
			<?php 	
		}
		
		function getVid()
		{
			global $post;
			$single=true;
			$vidKey='4sq_vid';
			$vid=get_post_meta($post->ID, $vidKey, $single );
			$string = $vid;
  			if(stristr($vid, 'http://foursquare.com/venue/') === FALSE) 
  			{
				return $vid;
  			}
  			else
  			{
  				$vid_rep=str_replace('http://foursquare.com/venue/', '', $vid);
  				return $vid_rep;
  			}
		}
		
		
		
		function getTodoToAdd()
		{	global $post;
			$postId=$post->ID;
			$single=true;
			$key='4sq_todo';
			$message=get_post_meta($postId, $key, $single);
			return $message;
		}
		
		
		function f4sq_todo_js_loader()
		{
			$foursq_plugin_url=get_bloginfo('wpurl')."/wp-content/plugins/wp-foursquare/js/4sqadd.js";
			if (!is_admin())
			{
	  			wp_enqueue_script('jquery');
	  			wp_enqueue_script('jquery-form');
	  			wp_register_script('addToFoursquare', $foursq_plugin_url);
	  			wp_enqueue_script('addToFoursquare');
	  			

			}
		}
		
		function jsAdder($content='')
		{	
			$vid=$this->getVid();
			$message=$this->getTodoToAdd();
			$dev_options=$this->get_admin_options();
			if ($dev_options['button_color'] == "light")
			{
				$img_url="http://foursquare.com/img/buttons/add_to_foursquare_light.png";
			}
			elseif ($dev_options['button_color'] == "dark")
			{
				$img_url="http://foursquare.com/img/buttons/add_to_foursquare_dark.png";
			}
			$message=urlencode($message);
			if ($message != "")
			{
			$content.='<p><a href="#" onclick="addToFoursquare('.$vid.',\''.$message.'\'); return false;"><img src="'.$img_url.'" /></a></p><br /><br />';
			}
			return $content;
		}
	}
}
//end of class, begin of out-of-class-scope section

if (class_exists("FoursquareTodoAdder"))
{
	$o4sqTdAdd= new FoursquareTodoAdder();
}
//actions and filters
function foursquare_meta_box()
		{
			add_meta_box('foursquare_vid', 'Add To Foursquare', 'vid_meta_callback' , 'post' );
		}
		
function vid_meta_callback()
{
	$foursq_nonce=wp_create_nonce('edit-4sq-nonce');
	echo '<h3>Venue ID (Number OR Full URL) :</h3> <input type="text" name="foursquare_todo_venue" value="" size="95" /> <br /> <br />';
	echo '<h3>Todo To Add:</h3> <input type="text" name="foursquare_todo_message" value="" size="95" /> <br />';
	echo '<input type="hidden" name="nonce-4sq-edit" value="'.$foursq_nonce.'" />';
}

function foursquare_save_meta()
{	
	$post_id=$_POST['post_ID'];
	$nonce=$_POST['nonce-4sq-edit'];
	$venue_id=$_POST['foursquare_todo_venue'];
	$todo_message=$_POST['foursquare_todo_message'];
	$old_venue_id=get_post_meta($post_id, '4sq_vid', TRUE);
	$old_message=get_post_meta($post_id, '4sq_message' , TRUE);
	if (wp_verify_nonce($nonce, 'edit-4sq-nonce'))
	{
			if ($old_venue_id != $venue_id)
			{
				if(isset($venue_id))
				{
				if(!empty($venue_id))
					{
					if($venue_id != '')
						{	delete_post_meta($post_id, '4sq_vid');
							add_post_meta($post_id, '4sq_vid', $venue_id);
						}
					}
				}
			}
			
			if ($old_message != $todo_message)
			{
				if(isset($todo_message))
				{
					if(!empty($todo_message))
					{
						if($todo_message != '')
						{	delete_post_meta($post_id, '4sq_todo');
							add_post_meta($post_id, '4sq_todo', $todo_message);
						}
					}
				}
			}
	}
}

if(!function_exists("foursquare_todo_adder_ap"))
{
	function foursquare_todo_adder_ap()
	{
		global $o4sqTdAdd;
		if(!isset($o4sqTdAdd))
		{
			return;
		}
		if (function_exists('add_options_page'))
		{
			add_options_page('Foursquare', 'Foursquare', 9, basename(_FILE_), array(&$o4sqTdAdd, 'print_admin_page'));
		}
	}
}


if (isset($o4sqTdAdd))
{
	//add an action to print the script out
	add_action('wp_head', array(&$o4sqTdAdd,'f4sq_todo_js_loader'), 1);
	add_action('activate_4sq-add-todo/4sq-add-todo.php', array(&$o4sqTdAdd, 'init'));
	
	if(is_admin())
	{
		add_action('admin_menu' , 'foursquare_todo_adder_ap');
		add_action('admin_menu' , 'foursquare_meta_box');
		add_action('edit_post' , 'foursquare_save_meta');
		add_action('save_post', 'foursquare_save_meta');
		add_action('publish_post' , 'foursquare_save_meta');
		
	}
	
	//add a filter to the_content to print the button out
	add_filter('the_content', array(&$o4sqTdAdd,'jsAdder'));
}
?>