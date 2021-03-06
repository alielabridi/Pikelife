<?php

/**
 * Fetch JPEG or PNG or GIF Image
 *
 * A custom function in PHP which lets you fetch jpeg or png images from remote server to your local server
 * Can also prevent duplicate by appending an increasing _xxxx to the filename. You can also overwrite it.
 *
 * Also gives a debug mode to check where the problem is, if this is not working for you.
 *
 * @author Swashata <swashata@intechgrity.com>
 * @copyright Do what ever you wish - I like GPL :) (& love tux ;))
 * @link http://www.intechgrity.com/?p=808
 *
 * @param string $img_url The URL of the image. Should start with http or https followed by :// and end with .png or .jpeg or .jpg or .gif. Else it will not pass the validation
 * @param string $store_dir The directory where you would like to store the images.
 * @param string $store_dir_type The path type of the directory. 'relative' for the location in relation with the executing script location or 'absolute'
 * @param bool $overwrite Set to true to overwrite, false to create a new image with different name
 * @param bool|int $pref internally used to prefix the extension in case of duplicate file name. Used by the trailing recursion call
 * @param bool $debug Set to true for enable debugging and print some useful messages.
 * @return string the location of the image (either relative with the current script or abosule depending on $store_dir_type)
 */
function itg_fetch_image($img_url, $store_dir = 'image', $store_dir_type = 'relative', $overwrite = false, $pref = false, $debug = false) {
    //first get the base name of the image
    $i_name = explode('.', basename($img_url));
    $i_name = $i_name[0];

    //now try to guess the image type from the given url
    //it should end with a valid extension...
    //good for security too
    if(preg_match('/https?:\/\/.*\.png$/i', $img_url)) {
        $img_type = 'png';
    }
    else if(preg_match('/https?:\/\/.*\.(jpg|jpeg)$/i', $img_url)) {
        $img_type = 'jpg';
    }
    else if(preg_match('/https?:\/\/.*\.gif$/i', $img_url)) {
        $img_type = 'gif';
    }
    else {
        if(true == $debug)
            echo 'Invalid image URL';
        return ''; //possible error on the image URL
    }

    $dir_name = (($store_dir_type == 'relative')? './' : '') . rtrim($store_dir, '/') . '/';

    //create the directory if not present
    if(!file_exists($dir_name))
        mkdir($dir_name, 0777, true);

    //calculate the destination image path
    $i_dest = $dir_name . $i_name . (($pref === false)? '' : '_' . $pref) . '.' . $img_type;

    //lets see if the path exists already
    if(file_exists($i_dest)) {
        $pref = (int) $pref;

        //modify the file name, do not overwrite
        if(false == $overwrite)
            return itg_fetch_image($img_url, $store_dir, $store_dir_type, $overwrite, ++$pref, $debug);
        //delete & overwrite
        else
            unlink ($i_dest);
    }

    //first check if the image is fetchable
    $img_info = @getimagesize($img_url);

    //is it a valid image?
    if(false == $img_info || !isset($img_info[2]) || !($img_info[2] == IMAGETYPE_JPEG || $img_info[2] == IMAGETYPE_PNG || $img_info[2] == IMAGETYPE_JPEG2000 || $img_info[2] == IMAGETYPE_GIF)) {
        if(true == $debug)
            echo 'The image doesn\'t seem to exist in the remote server';
        return ''; //return empty string
    }

    //now try to create the image
    if($img_type == 'jpg') {
        $m_img = @imagecreatefromjpeg($img_url);
    } else if($img_type == 'png') {
        $m_img = @imagecreatefrompng($img_url);
        @imagealphablending($m_img, false);
        @imagesavealpha($m_img, true);
    } else if($img_type == 'gif') {
        $m_img = @imagecreatefromgif($img_url);
    } else {
        $m_img = FALSE;
    }

    //was the attempt successful?
    if(FALSE === $m_img) {
        if(true == $debug)
            echo 'Can not create image from the URL';
        return '';
    }

    //now attempt to save the file on local server
    if($img_type == 'jpg') {
        if(imagejpeg($m_img, $i_dest, 100))
            return $i_dest;
        else
            return '';
    } else if($img_type == 'png') {
        if(imagepng($m_img, $i_dest, 0))
            return $i_dest;
        else
            return '';
    } else if($img_type == 'gif') {
        if(imagegif($m_img, $i_dest))
            return $i_dest;
        else
            return '';
    }

    return '';
}

//a quick test? just uncomment the line below
//echo itg_fetch_image('http://tuxpaint.org/stamps/stamps/animals/birds/cartoon/tux.png');
