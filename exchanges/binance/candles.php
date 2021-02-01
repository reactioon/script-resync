<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $interval="30m";
            $limit = 20;

            $pathCandles="https://api.binance.com/api/v1/klines?symbol=$symbol&interval={$interval}&limit={$limit}";

            $fileCandles=file_get_contents($pathCandles);

            $arrayCandles=false;

            $arrayData = array();

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                foreach($arrayCandles as $k => $v) {

                    $ds = date("Y-m-d H:i", ($v[0]/1000));
                    $de = date("Y-m-d H:i", ($v[6]/1000));

                    $arrayData[$k]["open"] = $v[1];
                    $arrayData[$k]["high"] = $v[2];
                    $arrayData[$k]["low"] = $v[3];
                    $arrayData[$k]["close"] = $v[4];
                    $arrayData[$k]["volume"] = $v[5];
                    $arrayData[$k]["date_start"] = date("Y-m-d H:i", strtotime($ds . " +3 hours"));
                    $arrayData[$k]["date_end"] = date("Y-m-d H:i", strtotime($de . " +3 hours 1 minute"));

                }

            }

            return $arrayData;

        }

        public function getPrices($symbol, $candles, $start, $end=false, $tickSize=8) {

            if (empty($candles[$start]["open"])) { return false; }

            $priceStart = $candles[$start]["open"];
            $priceEnd = (!$end) ? $candles[count($candles)-1]["close"] : $candles[$end]["close"];

            $dateStart = $candles[$start]["timestamp"];
            $dateEnd = (!$end) ? $candles[count($candles)-1]["timestamp"] : $candles[$end]["timestamp"];

            $objPrices = new stdClass;
            $objPrices->symbol = $symbol;
            $objPrices->start = number_format($priceStart,$tickSize, ".", "");
            $objPrices->end = number_format($priceEnd,$tickSize, ".", "");
            $objPrices->way = ($objPrices->end < $objPrices->start) ? "down" : "up";
            $objPrices->dateStart = $dateStart;
            $objPrices->dateEnd = $dateEnd;
            $objPrices->tickSize = $tickSize;

            return $objPrices;

        }

        /**
         * --- MONTH
         */

        public function getDataMonth($symbol, $tickSize) {

            $pathCandles="https://api.binance.com/api/v1/klines?symbol=$symbol&interval=2h";
            // $fileCandles=@file_get_contents($pathCandles);

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayData = array();

            if (!empty($arrayCandles)) {

                // $arrayCandles=json_decode($fileCandles, true);

                foreach($arrayCandles as $k => $v) {
                    $arrayData[$k]["open"] = $v[1];
                    $arrayData[$k]["close"] = $v[4];
                    $arrayData[$k]["timestamp"] = date("Y-m-d H:i:s", ($v[0]/1000));
                }

            }

            return $arrayData;
        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            $total = count($arraySymbols);

            $k=0;

            // construct data
            for($i=0; $i < $total; $i++) {

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

            $startTime = strtotime(date("Y-m-d 00:00:00"))*1000;
            $endTime = strtotime(date("Y-m-d 23:59:59"))*1000;
            $pathCandles = "https://api.binance.com/api/v1/klines?symbol=$symbol&interval=30m&limit=48&startTime=$startTime&endTime=$endTime";

            // $fileCandles=@file_get_contents($pathCandles);

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayData = array();

            if (!empty($arrayCandles)) {

                // $arrayCandles=json_decode($fileCandles, true);

                $y=0;

                foreach($arrayCandles as $y => $v) {
                    $arrayData[$y]["open"] = $v[1];
                    $arrayData[$y]["close"] = $v[4];
                    $arrayData[$y]["timestamp"] = date("Y-m-d H:i:s", ($v[0]/1000));
                    $y++;
                }

            }

            return $arrayData;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            // construct data
            $k=0;
            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataDay($symbol, $arraySymbols[$i]["tickSize"]);

                // var_dump($candles);

                $prices = $this->getPrices($symbol, $candles, 0, false, $arraySymbols[$i]["tickSize"]);

                // var_dump($prices);

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

                    // var_dump("oie");

                    $k++;

                }

                // exit;

            }

            return $arrayPrices;

        }

    }

?>