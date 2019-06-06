<?php

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Content-Type: application/json; charset=utf-8');
    
    $bookKeywords = htmlspecialchars($_GET['bookKeywords']) ?? '';
    // $bookKeywords = preg_replace('/　/', ' ', $bookKeywords);
    $index = (intval($_GET['page']) - 1) * 10 ?? 0;
    $url = 'https://www.googleapis.com/books/v1/volumes?q='.urlencode($bookKeywords).'&startIndex='.$index;

    // 出力
    $strJson = file_get_contents($url);

    header('Content-Type: application/json; charset=utf-8');
    echo $strJson;

