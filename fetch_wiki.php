<?php
function fetchWikiImage($keyword) {
    $url = 'https://commons.wikimedia.org/w/api.php?action=query&generator=search&gsrnamespace=6&gsrsearch=' . urlencode($keyword) . '&prop=imageinfo&iiprop=url&format=json&gsrlimit=1';
    
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: NnacoBot/1.0\r\n"
        ]
    ]);
    
    $json = file_get_contents($url, false, $context);
    if (!$json) return false;
    
    $data = json_decode($json, true);
    if (isset($data['query']['pages'])) {
        $pages = $data['query']['pages'];
        $first = reset($pages);
        if (isset($first['imageinfo'][0]['url'])) {
            return $first['imageinfo'][0]['url'];
        }
    }
    return false;
}

echo fetchWikiImage('cute notebook');
