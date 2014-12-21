<?php
exec("find . -type f -name '*.json'", $output);

foreach ($output as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json)) {
        echo $file.' is not a json'."\n";
    }
    $json = json_encode($json, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
    file_put_contents($file, $json);
}
