<?php
  
  define('DISCORD_WEBHOOK_URL', '');
  
  function message($msg) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, DISCORD_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
      'content' => $msg
    ]));
      
    curl_exec($ch);
    
    curl_close ($ch);
  }
  
  function process($line) {
    if (!preg_match("/Server thread\/INFO/", $line) or preg_match("/UUID of player|logged in with entity id|lost connection/", $line)) return;
    message(trim(@array_pop(explode("]:", $line, 2))));
    sleep(1);
  }
  
  $last_line_count = -1;
  
  for (;;) {
    $f = file(__DIR__ . '/logs/latest.log');
    
    $line_count = count($f);
    
    if ($last_line_count === -1) {
      $last_line_count = $line_count;
    }
    
    if ($last_line_count != $line_count) {
      if ($last_line_count < $line_count) {
        for ($i = $last_line_count; $i < count($f); $i++) {
          process($f[$i]);
        }
        $last_line_count = $line_count;
      } else {
        $line_count = 0;
      }
    }
    
    $last_line_count = $line_count;
    
    sleep(1);
  }