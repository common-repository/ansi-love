<?php

    //change GUID of file to png instead of ANSi
    //$pathinfo = pathinfo($post->guid);
    //$post->guid = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.png';
    //$post->post_mime_type = 'image/png'; //image/ansi ?
    //$newid = wp_insert_attachment( $post, $output ); //-wp_insert calls add_attachment?? no double entries in logs!

    //$meta = wp_generate_attachment_metadata( $newid, $output );
    //wp_update_attachment_metadata( $newid, $meta );
    //FB::log($meta);        

    
    add_filter( 'post_mime_types', array( &$this, 'add_ansi_mime_types') );  
    function add_ansi_mime_types( $post_mime_types ) 
    {   
        // select the mime type, here: 'application/pdf'  
        // then we define an array with the label values       
        $post_mime_types['image/ansi'] = array( __( 'ANSIs' ), __( 'Manage ANSIs' ), _n_noop( 'ANSI <span class="count">(%s)</span>', 'ANSIs <span class="count">(%s)</span>' ) );  
      
        // then we return the $post_mime_types variable  
        return $post_mime_types;  
    } 
      

    //add_filter( 'media_row_actions', array( &$this, 'hook_media_row_actions'), 10, 2 );//
   	function hook_media_row_actions( $actions, $post )
    {
    	//FB::log($actions);
    	
    	if ($post->post_mime_type == "image/ansi")
    	{
            //FB::log($post);
            //FB::log('unset');
    		
            //unset($actions['view']);

            //$wp_sizes = get_intermediate_image_sizes(); // custom sizes are included
            //$wp_sizes[] = "full";
            //foreach ($wp_sizes as $size)
            //{
            
            // try to set meta here
            /*
            $file = get_attached_file($post->ID);
            $pathinfo = pathinfo($file);
            $output = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.png';
            $meta = wp_generate_attachment_metadata( $post->ID, $output );
            $result = wp_update_attachment_metadata( $post->ID, $meta );
            */
            // get meta
            //$meta = wp_get_attachment_metadata( $post->ID );
            //$meta = get_post_meta( $post->id ); // $result = update_post_meta( $id, '_wp_attachment_metadata', $meta );
            //FB::log($meta);            


            //}


            //$file = get_attached_file( $post->ID );
    		//$actions['view'] = '<img src="'.plugins_url( 'ansilove/load_ansi.php' , __FILE__ ).'?input='.$file.'">';
    	}

		//$actions['file_url'] = '<a href="' . wp_get_attachment_url($post->ID) . '">Actual File</a>';
    	return $actions;
    }
?>