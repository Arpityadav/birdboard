<?php

function get_avatar($email)
{
    $email = md5($email);

    return "https://www.gravatar.com/avatar/{$email}?" . http_build_query([
        's' => 60,
        'd' => 'https://s3.amazonaws.com/laracasts/images/default-square-avatar.jpg'
    ]);
}
