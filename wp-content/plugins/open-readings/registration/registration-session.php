<?php

namespace OpenReadings\Registration\Registration_Session;

//Need to add option to select sql tables

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );


class ORRegistrationSession {
    public string $hash_id;
    public string $folder_hash;

    public function __construct(){
        $this->set_registration_cookies();
    }

    public function check_validity(){
        global $wpdb;
        $saved = $wpdb->get_var($wpdb->prepare(
            "SELECT saved FROM wp_or_registration_temp WHERE hash_id = %s",
            $_COOKIE['hash_id']
        ));
        if ($saved != 0)
            return false;

        if (isset($_COOKIE['hash_id']) and isset($_COOKIE['folder_hash']))
            if (hash('sha256', $_COOKIE['hash_id']) == $_COOKIE['folder_hash'])
                if(is_dir(WP_CONTENT_DIR . '/latex/temp/' . $_COOKIE['folder_hash']))
                    return true;
        return false;
    }
    public function set_registration_cookies(){
        
        if($this->check_validity()){
            $this->hash_id = $_COOKIE['hash_id'];
            $this->folder_hash = $_COOKIE['folder_hash'];
            return;
        }

        $this->hash_id = bin2hex(random_bytes(16)); 
        $this->folder_hash = hash('sha256', $this->hash_id);
        setcookie('hash_id', $this->hash_id, [
            'httponly' => true, 
            'secure' => true, // If using HTTPS
            'samesite' => 'Strict',
            'path' => '/'
        ]);
        
        setcookie('folder_hash', $this->folder_hash, [
            'httponly' => false, 
            'secure' => true,
            'samesite' => 'Strict',
            'path' => '/'
        ]);

        global $wpdb;

        $query = $wpdb->prepare(
            "INSERT INTO wp_or_registration_temp (hash_id, saved, last_export) VALUES (%s, %d, %s)",
            $this->hash_id, 0, current_time('mysql')
        );
        
        $wpdb->query($query);
    }
    public function setup_folder(){
        $folder = WP_CONTENT_DIR . '/latex/temp/' . $this->folder_hash;
        if(!is_dir($folder))
            mkdir($folder);
        $image_folder = $folder . '/images';
        if(!is_dir($image_folder))
            mkdir($image_folder);
        copy(WP_CONTENT_DIR . '/latex/orstylet.sty', $folder . '/orstylet.sty');
        copy(WP_CONTENT_DIR . '/latex/default/nftmc-1024x631-1.jpg', $folder . '/nftmc-1024x631-1.jpg');
        copy(WP_CONTENT_DIR . '/latex/default/abstract.pdf', $folder . '/abstract.pdf');
        copy(WP_CONTENT_DIR . '/latex/default/abstract.log', $folder . '/abstract.log');

    }
}

class ORLatex {
}