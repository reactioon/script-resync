<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $interval="M30";
            $limit = 20;
            $sort = "desc";

            $date_start = date("Y-m-d H:i:s");

            $date_end = date("Y-m-d H:i:s", strtotime($date_start . " -1 hour"));

            $date_start = strtotime($date_start);
            $date_end = strtotime($date_end);

            $pathCandles="https://api.hitbtc.com/api/2/public/candles/$symbol?period={$interval}&limit={$limit}&sort={$sort}";

            $fileCandles=@file_get_contents($pathCandles);

            $arrayCandles=array();
            $arrayData=array();

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                foreach($arrayCandles as $k => $v) {

                    if (!empty($v["open"])) {

                        $ds = date("Y-m-d H:i", strtotime($v["timestamp"]));
                        $ds = date("Y-m-d H:i", strtotime($ds . " +3 hour"));

                        $arrayData[$k]["open"] = $v["open"];
                        $arrayData[$k]["high"] = $v["max"];
                        $arrayData[$k]["low"] = $v["min"];
                        $arrayData[$k]["close"] = $v["close"];
                        $arrayData[$k]["volume"] = $v["volume"];
                        $arrayData[$k]["date_start"] = $ds;
                        $arrayData[$k]["date_end"] = date("Y-m-d H:i", strtotime($ds . " +30 minutes"));

                    }

                }

            }

            return $arrayData;

        }

        public function getPrices($symbol, $candles, $start, $end=false, $tickSize=8) {

            if (empty($candles[$start]["open"])) { return false; }

            $priceEnd = $candles[$start]["open"];
            $priceStart = (!$end) ? $candles[count($candles)-1]["close"] : $candles[$end]["close"];

            $dateEnd = $candles[$start]["timestamp"];
            $dateStart = (!$end) ? $candles[count($candles)-1]["timestamp"] : $candles[$end]["timestamp"];

            $objPrices = new stdClass;
            $objPrices->symbol = $symbol;
            $objPrices->start = number_format($priceStart,$tickSize, ".", "");
            $objPrices->end = number_format($priceEnd,$tickSize, ".", "");
            $objPrices->way = ($objPrices->end < $objPrices->start) ? "down" : "up";
            $objPrices->dateStart = $dateStart;
            $objPrices->dateEnd = $dateEnd;

            return $objPrices;

        }

        /**
         * --- MONTH
         */

        public function getDataMonth($symbol, $tickSize) {

            $pathCandles="https://api.hitbtc.com/api/2/public/candles/$symbol?period=D1&limit=30&sort=DESC";
            // $fileCandles=@file_get_contents($pathCandles);

            // $arrayCandles=false;

            // if (!empty($fileCandles)) {
            //     $arrayCandles=json_decode($fileCandles, true);
            // }

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            return $arrayCandles;

        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            $k = 0;

            // construct data
            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataMonth($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, 0, false, $arraySymbols[$i]["tickSize"]);

                if (!empty($prices)) {

                    $percentageDiff = @abs(number_format((1 - $prices->end / $prices->start) * 100, 2));

                    $prices->percentage = $percentageDiff;

                    $arrayPrices[$k] = new stdClass;

                    $arrayPrices[$k]->prices = new stdClass;
                    $arrayPrices[$k]->symbol = new stdClass;

                    $arrayPrices[$k]->exchange = $exchange;
                    $arrayPrices[$k]->type = "month";
                    $arrayPrices[$k]->prices = $prices;
                    $arrayPrices[$k]->symbol = $arraySymbols[$i];

                    $k++;

                }

            }

            return $arrayPrices;

        }

        /**
         * --- DAY
         */

        public function getDataDay($symbol, $tickSize) {

            $pathCandles="https://api.hitbtc.com/api/2/public/candles/$symbol?period=M30&limit=48&sort=DESC";
            // $fileCandles=@file_get_contents($pathCandles);

            // $arrayCandles=false;

            // if (!empty($fileCandles)) {
            //     $arrayCandles=json_decode($fileCandles, true);
            // }

            $arrayCandles = $this->koin->requestJSON($pathCandles);
            return $arrayCandles;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            $k = 0;

            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataDay($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, 0, false, $arraySymbols[$i]["tickSize"]);

                if (!empty($prices)) {

                    $percentageDiff = @abs(number_format((1 - $prices->end / $prices->start) * 100, 2));

                    $prices->percentage = $percentageDiff;

                    $arrayPrices[$k] = new stdClass;

                    $arrayPrices[$k]->prices = new stdClass;
                    $arrayPrices[$k]->symbol = new stdClass;

                    $arrayPrices[$k]->exchange = $exchange;
                    $arrayPrices[$k]->type = "day";
                    $arrayPrices[$k]->prices = $prices;
                    $arrayPrices[$k]->symbol = $arraySymbols[$i];

                    $k++;

                }

            }

            return $arrayPrices;

        }

    }

?>