<?php
require_once 'config/db_config.php';

class Database
{
    public $connect;

    public function databaseConnect()
    {
        try {
            $this->connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (Exception $exception) {
            return die("Ошибка подключения к Базе данных: " . $exception->getMessage());
        }

        return $this->connect;

    }

    public function databaseConnectClose()
    {
        $this->connect->close();
        return $this->connect;
    }

    public function createTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `Products` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`article` varchar(200) NOT NULL,
	`product_name` varchar(200) NOT NULL,
	`price` INT NOT NULL,
	PRIMARY KEY (`id`)
)";
            $this->connect->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `Description` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`product_article` varchar(200) NULL,
	`characteristic` varchar(200) NULL,
	`description` varchar(200) NULL,
	PRIMARY KEY (`id`)
)";
            $this->connect->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `Photos` (
            `id` INT NOT NULL AUTO_INCREMENT,
	`product_article` varchar(200) NULL,
	`photo` varchar(200) NULL,
	PRIMARY KEY (`id`)
);";
            $this->connect->query($sql);

            $sql = "ALTER TABLE `Description` ADD CONSTRAINT `description_fk0` FOREIGN KEY (`product_article`) REFERENCES `Products`(`article`)";
            $this->connect->query($sql);

            $sql = "ALTER TABLE `Photos` ADD CONSTRAINT `Photos_fk0` FOREIGN KEY (`product_article`) REFERENCES `Products`(`article`);";
            $this->connect->query($sql);
        } catch (Exception $exception) {
            echo "Ошибка создания таблицы: " . $exception->getMessage();
        }
        return $this->connect;
    }

    public function dropTable()
    {

        try {
            $sql = "DROP TABLE IF EXISTS `Products`, `Description`";
            $this->connect->query($sql);
        } catch (Exception $exception) {
            echo "Ошибка удаления таблицы: " . $exception->getMessage();
        }

        return $this->connect;
    }
}