<?php
    namespace Emojione;
    require_once('init.php');

    $curl_url = '/api/3.0/posts/create.json';
    $author_name = $_POST['name'];
    $author_email = $_POST['email'];
    $author_url = $_POST['url'] == '' || $_POST['url'] == 'null' ? null : $_POST['url'];

    if( $author_name == $username && $author_email == $email && strpos($session, 'session') !== false ){
        $author_name = null;
        $author_email = null;
        $author_url = null;
    }

    $post_message = $client->shortnameToUnicode($_POST['message']);

    $post_data = array(
        'api_key' => $public_key,
        'thread' => $_POST['thread'],
        'parent' => $_POST['parent'],
        'message' => $post_message,
        'author_name' => $author_name,
        'author_email' => $author_email,
        'author_url' => $author_url
        //'ip_address' => $_SERVER["REMOTE_ADDR"]
    );
    $data = curl_post($curl_url, $post_data);

    $post = $data -> response;
    $content = $data -> code != 0 ? $post : post_format($post);

    $output = array(
       'code' => $data -> code,
       'thread' => $post -> thread,
       'response' => $content
    );

    if ( $_POST['parent'] != '' && $data -> code == 0 ){
        $mail_query = array(
            'parent'=> $_POST['parent'],
            'id'=> $post -> id,
            'link'=> $_POST['link'],
            'title'=> $_POST['title']
        );
        $mail = curl_init();
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $curl_opt = array(
            CURLOPT_URL => $protocol.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/sendemail.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $mail_query,
            CURLOPT_TIMEOUT => 1
        );
        curl_setopt_array($mail, $curl_opt);
        curl_exec($mail);
        curl_close($mail);
    }
    print_r(json_encode($output));
