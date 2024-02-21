<?php
header('Content-Type: text/html; charset=UTF-8');
require 'phpQuery.php';
require 'database.php';
$database = new Database();
$database->databaseConnect();
$database->createTable();
$database->databaseConnectClose();
function parser($start, $end, $counter)
{
    $arr = [];
    $url = 'https://paletka.by/catalog/uhod-za-kojey-lica/page-' . $start;
    $database = new Database();
    $database->databaseConnect();
    if ($start <= $end) {
        $file = file_get_contents($url);
        $document = phpQuery::newDocument($file);
        foreach ($document->find('.catalog-products .catalog-item') as $item) {
            $item = pq($item);
            $id = intval($item->find('.quick-view')->attr('data-id'));
            $photo = 'https://paletka.by' . $item->find('.catalog-item-image img')->attr('src');
            $product_name = $item->find('.catalog-item-name')->html();
            $price = intval($item->find('.price span')->html());
            $sql = "INSERT INTO Parser (id, photo, product_name, price) VALUES ('$id', '$photo', '$product_name', '$price')";
            if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
            $myCurl = curl_init();
            curl_setopt($myCurl, CURLOPT_URL, "https://paletka.by/product/quick");
            curl_setopt($myCurl, CURLOPT_POST, 1);
            curl_setopt($myCurl, CURLOPT_POSTFIELDS,
                "id=$id");
            curl_setopt($myCurl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($myCurl);
            curl_close($myCurl);
            $modal = phpQuery::newDocument($response);
            $table = $modal->find('.modal-content .specifications .overflow .specifications-table table tr td');
            foreach ($table as $col) {
                $col = pq($col);
                $td = $col->html();
                if ($counter % 2 !== 0) {
                    $arr[] = $td;
                    $counter++;
                } else {
                    $arr[] = $td;
                    $sql = "INSERT INTO Description (product_id, characteristic, description) VALUES ('$id', '$arr[0]', '$arr[1]')";
                    if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
                    $arr = [];
                    $counter++;

                }
            }
        }
        $start++;
        parser($start, $end, $counter);
        $database->databaseConnect();
    }

}

$counter = 1;
$start = 1;
$end = 2;

parser($start, $end, $counter);

echo "<h1 style='text-align: center'>УСПЕШНО РАСПАРШЕНЫ $end СТРАНИЦ(Ы) В БД</h1>";