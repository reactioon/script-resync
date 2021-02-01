<?php

    // basic vars
    define('PATH_INTERFACES', BASE_PATH.'/scripts/resync/interfaces');
    define('PATH_EXCHANGES', BASE_PATH.'/scripts/resync/exchanges');
    define('PATH_HELPERS', BASE_PATH.'/scripts/resync/libs/helpers');

    // load interfaces
    require_once(PATH_INTERFACES . "/candles.php");
    require_once(PATH_INTERFACES . "/symbols.php");
    require_once(PATH_INTERFACES . "/trades.php");
    require_once(PATH_INTERFACES . "/tickers.php");

    // load helpers
    require_once(PATH_HELPERS . "/koins.php");

    // include bin
    require_once("bin.php");

    class koins {

        public function __construct() {

            $this->memcache = new Memcache;
            $this->memcache->connect('localhost', 11211) or die ("Could not connect");

            date_default_timezone_set("America/Fortaleza");

        }

        public function setExchange($exchange) {
            $this->exchange = $exchange;
        }

        public function start($action, $exchange, $currency, $hash, $returnFormat="temp", $expire=5) {

            $this->exchange = $exchange;
            $this->returnFormat = $returnFormat;
            $this->hashSync = $hash;
            $this->expire_minutes = $expire;

            $keyCache = $exchange . "." . $action . "." . $currency;

            $checkCache = $this->memcache->get($keyCache);

            $now = strtotime(date("Y-m-d H:i:s"));
            $expire = strtotime($checkCache);

            if ($checkCache) {
                $waiting = ($now < $expire) ? true : false;
            }

            if (empty($checkCache) || !$waiting) {

                return $keyCache;

            } else {

                $bin = new bin();
                $bin->load($this->exchange);
                $bin->showResume(false, $this->exchange, $action, $currency, microtime(true), false, $hash, $returnFormat, $checkCache);

                return false;

            }

        }

        public function end($keyCache) {

            $arrayKey = explode(".", $keyCache);

            $type = $arrayKey[1];

            // switch($type) {
            //     case "symbols":
            //         $this->setCacheTime($keyCache);
            //     break;
            //     case "tickers":
            //         $this->setCacheTime($keyCache);
            //     break;
            //     case "candlesmonth":
            //         $this->setCacheTime($keyCache);
            //     break;
            //     case "candlesday":
            //         $this->setCacheTime($keyCache);
            //     break;
            // }

            $this->setCacheTime($keyCache);

        }

        public function setCacheTime($keyCache) {
            $this->memcache->set($keyCache, $this->date_expire, false, $this->expire_seconds);
        }

        public function getExpireTime() {

            $minutes = $this->expire_minutes;
            return $minutes;

        }

        public function setExpireTime($type) {

            $minutes = $this->getExpireTime();

            $this->expire_seconds = $minutes*60;
            $this->date_expire = date("Y-m-d H:i:s", strtotime("+$minutes minutes"));

            return true;

        }

        public function run($key, $currency, $type) {

            $expire = $this->getExpireTime($type);

            $starttime = microtime(true);

            $bin = new bin();
            $bin->load($this->exchange);

            $dirBaseData = BASE_PATH."/data/resync/data";
            $arrayFiles = scandir($dirBaseData);

            foreach($arrayFiles as $k => $v) {

                if ($v != ".." && $v != ".") {

                    $expName = explode("-", $v);

                    $key = $expName[0] . "-" . $expName[1];

                    $keyFile = $this->exchange . "-" . $type . "-" . $currency;

                    if ($key == $keyFile) {
                        return;
                    }

                }

            }

            if ($key && $this->setExpireTime($type)) {
                $this->setCacheTime($key);
            }

            if (strpos($type, ":") !== false) {

                $arrType = explode(":", $type);

                $pair = $arrType[1];
                $type = $arrType[0];

            } else {

                $pair = $currency;

            }

            $arrTypesAllowedSymbol = array('symbols', 'tickers', 'candlesmonth', 'candlesday');

            $arraySymbols = array();

            if (in_array($type, $arrTypesAllowedSymbol)) {
                $arraySymbols = $bin->symbols->getAll($pair);
            }

            switch($type) {
                case "symbols":
                    $data = $arraySymbols;
                break;
                case "tickerBySymbol":
                    $data = $bin->tickers->getBySymbol($currency);
                break;
                case "tickers":
                    $data = $bin->tickers->getTickers($arraySymbols, $this->exchange, $pair);
                break;
                case "candlesmonth":
                    $data = $bin->candles->getMonth($bin, $arraySymbols, $this->exchange);
                break;
                case "candlesday":
                    $data = $bin->candles->getDay($bin, $arraySymbols, $this->exchange);
                break;
                case "candlesLast":
                    $data = $bin->candles->getCandlesLastBySymbol($currency);
                break;
            }

            $save = $bin->saveData($this->exchange, $type, $data, $currency);
            $bin->showResume($data, $this->exchange, $type, $currency, $starttime, $save, $this->hashSync, $this->returnFormat, $this->date_expire);

            return $expire;


        }

    }

?>
