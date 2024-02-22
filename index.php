<?php
header('Content-Type: text/html; charset=UTF-8');
require 'phpQuery.php';
require 'database.php';
$database = new Database();
$database->databaseConnect();
$database->createTable();
$database->databaseConnectClose();

function imageDownload($url, $name)
{
    $Headers = @get_headers($url);
    if (preg_match("|200|", $Headers[0])) {
        $image = file_get_contents($url);
        file_put_contents(dirname(__FILE__) . "/uploads/img/". $name .".jpg", $image);
        return "/uploads/img/". $name .".jpg";
    } else {
        return "Не найдено";
    }
}

function parser($start, $end, $counter, $image_name)
{
    $arr = [];
    $main_url = 'https://paletka.by';
    $url = 'https://paletka.by/catalog/uhod-za-kojey-lica/page-' . $start;
    $database = new Database();
    $database->databaseConnect();
    if ($start <= $end) {
        $file = file_get_contents($url);
        $document = phpQuery::newDocument($file);

        foreach ($document->find('.catalog-products .catalog-item') as $item) {
            $item = pq($item);
            $url_item = $item->find('a')->attr('href');
            $new_url = $main_url . $url_item;
            $product_item = file_get_contents($new_url);
            $doc = phpQuery::newDocument($product_item);
            $doc = pq($doc);
            $product_name = trim($doc->find('h1')->html());
            $price = trim(intval($doc->find('.price-normal')->html()));
            $article = $doc->find('.item-art')->html();
            $article = str_replace('Арт.', '', $article);
            $article = str_replace('Код товара:', '', $article);
            $article = trim($article);
            $sql = "INSERT INTO Products (article, product_name, price) VALUES ('$article', '$product_name', '$price')";
            if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
            $images = $doc->find('.product-left__images a');
            if ($images == "") {
                $img = $doc->find('.product-slider a')->attr('href');
                $img = $main_url . $img;
                imageDownload($img, $image_name);
                $img_url = "/uploads/img/". $image_name .".jpg";
                $image_name++;
                $sql = "INSERT INTO Photos (product_article, photo) VALUES ('$article', '$img_url')";
                if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
            } else {
                foreach ($images as $image) {
                    $image = pq($image);
                    $image_item = $image->find('img')->attr('src');
                    $image_item = $main_url . $image_item;
                    imageDownload($image_item, $image_name);
                    $img_url = "/uploads/img/". $image_name .".jpg";
                    $image_name++;
                    $sql = "INSERT INTO Photos (product_article, photo) VALUES ('$article', '$img_url')";
                    if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
                }
            }
            $characteristics_table = $doc->find('.products-tabs .tab-2 table tr td');
            foreach ($characteristics_table as $col) {
                $col = pq($col);
                $td = $col->html();
                if ($counter % 2 !== 0) {
                    $arr[] = trim($td);
                    $counter++;
                } else {
                    $arr[] = trim($td);
                    $sql = "INSERT INTO Description (product_article, characteristic, description) VALUES ('$article', '$arr[0]', '$arr[1]')";
                    if ($database->connect->query($sql) === false) echo "Ошибка: " . $database->connect;
                    $arr = [];
                    $counter++;

                }
            }
        }
        $start++;
        parser($start, $end, $counter, $image_name);
        $database->databaseConnect();
    }

}

$counter = 1;
$image_name = 1;
$start = 1;
$end = 1;

parser($start, $end, $counter, $image_name);

echo "<h1 style='text-align: center'>УСПЕШНО РАСПАРШЕНЫ $end СТРАНИЦ(Ы) В БД</h1>";