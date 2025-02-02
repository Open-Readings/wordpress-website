<?php

namespace OpenReadings\Registration\Registration_Session;

//Need to add option to select sql tables

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );


class ORRegistrationSession {
    public string $hash_id;
    public string $folder_hash;

    public function __construct(){
        global $wpdb;
        $id = isset($_GET['id']) ? ($_GET['id']) : 0;
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT session_id FROM wp_or_registration_presentations WHERE person_hash_id = %s",
            $id
        ));
        if (count($result) > 0){
            $folder_hash = $result[0]->session_id;
            setcookie('hash_id', $id, [
                'httponly' => true, 
                'secure' => true, // If using HTTPS
                'samesite' => 'Strict',
                'path' => '/'
            ]);
            
            setcookie('folder_hash', $folder_hash, [
                'httponly' => false, 
                'secure' => true,
                'samesite' => 'Strict',
                'path' => '/'
            ]);
            $_COOKIE['hash_id'] = $id;
            $_COOKIE['folder_hash'] = $folder_hash;
            $result = $wpdb->get_results($wpdb->prepare(
                "SELECT saved FROM wp_or_registration_temp WHERE hash_id = %s",
                $id
            ));
            $this->copy_files_to_temp($folder_hash);
            if (count($result) > 0)
                $result = $wpdb->query($wpdb->prepare(
                    "UPDATE wp_or_registration_temp SET saved = %d, last_export = %s WHERE hash_id = %s",
                     0, current_time('mysql'), $id
                ));
            else
                $result = $wpdb->query($wpdb->prepare(
                    "INSERT INTO wp_or_registration_temp (hash_id, saved, last_export) VALUES (%s, %d, %s)",
                    $id, 0, current_time('mysql')
                ));

            if($this->check_validity()){
                $this->hash_id = $_COOKIE['hash_id'];
                $this->folder_hash = $_COOKIE['folder_hash'];
                return;
            }
            
        }
            
        $this->set_registration_cookies();
    }

    public static function copy_files_to_temp($folder_hash){
        $temp_folder = WP_CONTENT_DIR . '/latex/temp/' . $folder_hash;
        $perm_folder = WP_CONTENT_DIR . '/latex/perm/' . $folder_hash;

        if(!is_dir($temp_folder))
            mkdir($temp_folder);

        if(!is_dir($temp_folder . '/images'))
            mkdir($temp_folder . '/images');

        foreach(scandir($perm_folder) as $file){
            if ($file == '.' or $file == '..' or $file == 'images')
                continue;
            copy($perm_folder . '/' . $file, $temp_folder . '/' . $file);
        }

        foreach(scandir($perm_folder . '/images') as $file){
            if ($file == '.' or $file == '..')
                continue;
            copy($perm_folder . '/images/' . $file, $temp_folder . '/images/' . $file);
        }
        copy(WP_CONTENT_DIR . '/latex/default/orstylet.sty', $temp_folder . '/orstylet.sty');


    }

    public function check_validity(){
        global $wpdb;
        if(!isset($_COOKIE['hash_id']) or !isset($_COOKIE['folder_hash']))
            return false;
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
            if(!$this->is_update($_COOKIE['hash_id'])){
                $this->hash_id = $_COOKIE['hash_id'];
                $this->folder_hash = $_COOKIE['folder_hash'];
                return;
            }
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
        $this->setup_folder();
    }
    public function setup_folder(){
        $folder = WP_CONTENT_DIR . '/latex/temp/' . $this->folder_hash;
        if(!is_dir($folder))
            mkdir($folder);
        $image_folder = $folder . '/images';
        if(!is_dir($image_folder))
            mkdir($image_folder);
        copy(WP_CONTENT_DIR . '/latex/default/orstylet.sty', $folder . '/orstylet.sty');
        copy(WP_CONTENT_DIR . '/latex/default/nftmc-1024x631-1.jpg', $folder . '/nftmc-1024x631-1.jpg');
        copy(WP_CONTENT_DIR . '/latex/default/abstract.pdf', $folder . '/abstract.pdf');
        copy(WP_CONTENT_DIR . '/latex/default/abstract.log', $folder . '/abstract.log');

    }
    public function is_update($hash_id){
        global $wpdb;
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_or_registration WHERE hash_id = %s",
            $hash_id
        ));

        if (count($result) > 0)
            return true;

        return false;
    }
}
