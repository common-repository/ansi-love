<?php
/*
Plugin Name: Ansi-Love
Plugin URI: http://www.opicron.eu/
Description: Ansi Love implementation for Wordpress
Author: Robbert Langezaal
Author URI: http://www.opicron.eu/
Version: 0.1
Tags: ansi, scene, artwork, ansilove
License: GPL2
*/

/*
Copyright (C) 2012  Robbert Langezaal

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


new wp_ansilove;
	
class wp_ansilove {

    var $uploads = array();

	function wp_ansilove()
	{
		$this->__construct();		
	} 

	function __construct()
	{	
        //FB::log('test');
        
		add_action( 'admin_init', array( &$this, 'admin_init' ) );			
		add_filter( 'upload_mimes', array( &$this, 'add_ansi_upload_mime') );  
        add_filter( 'add_attachment', array( &$this, 'add_ansi_attachment'), 1, 2);
        add_filter( 'wp_handle_upload_prefilter', array( &$this, 'custom_upload_name') );  

        // edit attachment
        add_filter( 'media_row_actions', array( &$this, 'hook_media_row_actions'), 10, 2 );
        //add_filter( 'attachment_fields_to_save', array(&$, 'add_image_attachment_fields_to_save'), null , 2);

        // how to hook media delete?
        add_filter( 'delete_attachment', array( &$this, 'delete_ansi_attachment') );  
        add_filter( 'wp_trash_post', array( &$this, 'trash_ansi_attachment') );  
	}
    
    function delete_ansi_attachment( $id )
    {
        $file = get_post_meta( $id, 'ansi_file', true );                

        if (file_exists($file))
            unlink($file);
    }
      
    function custom_upload_name( $file )
    {
        //FB::log($file);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (strcasecmp($ext,"ans") !== false)
        {
            $pathinfo = pathinfo($file['name']);
            $newfile = $pathinfo['filename'].'.png';    
            
            $file['name'] = $newfile;
            $this->uploads[] = preg_replace("/\d+$/","",$pathinfo['filename']);

        }        
        return $file;
    }

	function admin_init()
	{        
		require_once( plugin_dir_path(__FILE__).'ansilove/ansilove.php');    	
        $this->process_actions();
    }        

    function process_actions()
    {        
        global $pagenow;

        if ( 'post.php' == $pagenow && isset( $_GET['transparent'] ) )
        {
            $id = (int)$_GET['post'];
            $file = get_post_meta( $id, 'ansi_file', true );                
            $pathinfo = pathinfo($file);
            $image = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.png';            

            //FB::log($id);
            //FB::log($file);
            if (file_exists($file))
            {
                if ( $_GET['transparent'] == 'true' )                  
                {
                    load_ansi($file,$image,'80x25','transparent',1);
                    update_post_meta( $id, 'ansi_transparent', true );                
                }
                else
                {
                    load_ansi($file,$image,'80x25',9,1);
                    update_post_meta( $id, 'ansi_transparent', false );                
                }
            }

            // recalculate image size and set new meta data
            $meta = wp_generate_attachment_metadata( $id, $image );
            $result = wp_update_attachment_metadata( $id, $meta );            

        }
        
    }

    function add_ansi_upload_mime( $mime_types ) 
    {   
        $mime_types['asc'] = 'image/ascii';        
    	$mime_types['ans'] = 'image/ansi';
    	return $mime_types;
    }

function multi_array_key_exists( $needle, $haystack ) {
    if (is_array($haystack))
    {
        foreach ( $haystack as $key => $value ) :

            if ( $needle == $key )
                return true;
           
            if ( is_array( $value ) ) :
                 if ( $this->multi_array_key_exists( $needle, $value ) == true )
                    return true;
                 else
                     continue;
            endif;           
        endforeach;
    }
    return false;
}   

    function hook_media_row_actions( $actions, $post )
    {
        $file = get_post_meta( $post->ID, 'ansi_file', true );                
        $transparent = get_post_meta( $post->ID, 'ansi_transparent', true );             

        /** 
            do not show transparancy options if resized original!
         */
        // if _wp_attachment_backup_sizes exists do not show 
        $meta = get_post_meta($post->ID, '_wp_attachment_backup_sizes');
        //FB::log($meta);

        if (!$this->multi_array_key_exists("full-orig",$meta)==true)
        {
            if (file_exists($file))
            {
                if ($transparent)
                {
                    //send attachment to edit page 
                    $extra = '&transparent=false';
                    $actions['ansi-love-transparent'] = '<a href="' . get_edit_post_link($post->ID, true) . $extra . '" title="Solid background" rel="permalink">' . __('Solid') . '</a>';
                }
                else
                {
                    //send attachment to edit page 
                    $extra = '&transparent=true';
                    $actions['ansi-love-transparent'] = '<a href="' . get_edit_post_link($post->ID, true) . $extra . '" title="Transparent background" rel="permalink">' . __('Transparent') . '</a>';
                }
            }
        }
        //$meta = wp_get_attachment_metadata( $post->ID );
        //$file = get_post_meta( $post->ID, 'ansi_file', true );                
        //FB::log($file);
        //
        return $actions;
    }

    function add_ansi_attachment( $id )    
    {
        // get filename and tag 
        $file = get_attached_file($id);
        $pathinfo = pathinfo($file);
        $filename = $pathinfo['filename'].'.png';

        // we use this tag to know if an ANSI has been uploaded
        $tag = preg_replace("/\d+$/","",$pathinfo['filename']);
        
        // find tag in uploads
        $pos = array_search($tag, $this->uploads);
        if ( $pos !== false )
        {   
            // remove current ansi file name from uploaded array         
            unset($this->uploads[$pos]);
            $post = get_post($id);        
            
            // rename upload back into ANSI extension
            $upload = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.png';            
            $tempfile = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.ans';
            rename($upload, $tempfile);

            // create PNG from ANSI
            load_ansi($tempfile,$upload,'80x25',9,1);

            //add original ansi file to remove later / or reference with shortcode
            update_post_meta( $id, 'ansi_file', $tempfile );                
            update_post_meta( $id, 'ansi_transparent', false );                               
        }
    }
} // class

?>