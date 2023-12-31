<?php

function amazonScrappe($search, $order='', $condition='', $page=1) {
    $url = "https://www.amazon.com/s?k=" . urlencode($search);
    if ($order != '' ) {
        if ($order === 'price-asc') {
            $url  .= '&s=price-asc-rank';
         } elseif ($order === 'price-desc') {
             $url .= '&s=price-desc-rank';
         } elseif ($order === 'most-recent'){
             $url .= '&s=date-desc-rank';
         } elseif ($order === 'review-rank') {
             $url .= '&s=review-rank';
         }
    }

    if ($condition != '') {
        if ($condition === 'new') {
            $url .= '&rh=p_n_condition-type%3A2224366011';
        } elseif ($condition === 'used') {
            $url .= '&rh=p_n_condition-type%3A2224368011';
        }
    }
    
    if ($page !== 1) {
        $url .= '&page='.$page.'&ref=sr_pg_' . $page-1;
    }

    var_dump($url);
  

    // Set the user-agent details
    $opts = array(
        'http'=>array(
        'method'=>"GET",
        'header'=>"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3\r\n"
        )
    );
    
    // Create a stream context
    $context = stream_context_create($opts);
    $html = file_get_contents($url, false, $context);


    // Create a DOMDocument object and load the HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Create an XPath object to query the DOM
    $xpath = new DOMXPath($dom);

    $total_pages = $xpath->query("//span[@class='s-pagination-item s-pagination-disabled']");
    #get the value contained within the first span of the DOMNOdeList total_pages
    $total_pages = ($total_pages[0]) ? intval($total_pages[0]->textContent) : null;
    //var_dump($total_pages);
    // Initialize an array to store the scraped product data
    $products = [];

    // Extract product information using XPath queries
    $titles = $xpath->query("//span[@class='a-size-medium a-color-base a-text-normal']");
    $images = $xpath->query("//img[@class='s-image']");
    $links = $xpath->query("//a[@class='a-link-normal s-underline-text s-underline-link-text s-link-style a-text-normal']");
    $prices = $xpath->query("//span[@class='a-offscreen']");

    // Iterate over the extracted data and populate the products array
    for ($i = 0; $i < $titles->length; $i++) {
        if ($prices[$i] != null) {
            $price = $prices[$i]->textContent;
        } else {
            $price = "Not specified";
        }
        $product = [
            'title' => $titles[$i]->textContent,
            'image' => $images[$i]->getAttribute('src'),
            'link' => $links[$i]->getAttribute('href'),
            'price' => $price
        ];
    
         $products[] = $product;
    }

    if($total_pages > 1) {
        return array(
            'products' => $products,
            'total_pages' => $total_pages
        );
    }
    return $products;

}

?>