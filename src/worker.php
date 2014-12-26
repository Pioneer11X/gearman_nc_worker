<?php
$worker = new GearmanWorker();
$worker->addServer("119.81.61.166", "4730");
$count = 0;
$worker->addFunction("ebay_crawler", "get_ebay_data", $count);
while ($worker->work());

function get_ebay_data($job, &$count) {
    $data_json = $job->workload();
    $data_array = json_decode($data_json, true);
    
    $url = $data_array['url'];
    $id = $data_array['id'];
    $identifier = $data_array['identifier'];

    
    $orig_url = $url;
    $curl = curl_init();
    if(strpos($url, '"')) $url = str_replace('"', '%22', $url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_ENCODING, "gzip,deflate");
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36');
    $data = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    $header = substr($data, 0, $header_size);
    $data = substr($data, $header_size-1);
    curl_close($curl);
    preg_match_all('/^Location:(.*)$/mi', $header, $matches);
    $location = !empty($matches[1]) ? trim($matches[1][0]) : '';

    $return = array();
    $return['data'] = $data;
    $return['header'] = $header;
    $return['status_code'] = $status_code;
    $return['location'] = $location;
    $return['content_type'] = $content_type;
    $return['url'] = $orig_url;
    $return['id'] = $id;
    $return['identifier'] = $identifier;


    return json_encode($return);
}
