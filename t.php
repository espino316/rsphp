<?php
//print_r(openssl_get_cipher_methods());
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = openssl_random_pseudo_bytes($ivlen);
print_r(base64_encode($iv));
