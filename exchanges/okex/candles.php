<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getPrices($symbol, $candles, $start, $end=false, $tickSize=8) {

            if (empty($candles[$start]["open"])) { return false; }

            $priceStart = $candles[$start]["open"];
            $priceEnd = (!$end) ? $candles[count($candles)-1]["close"] : $candles[$end]["close"];

            $dateStart = $candles[$start]["timestamp"];
            $dateEnd = (!$end) ? $candles[count($candles)-1]["timestamp"] : $candles[$end]["timestamp"];

            $objPrices = new stdClass;
            $objPrices->symbol = $symbol;
            $objPrices->start = number_format($priceStart,$tickSize);
            $objPrices->end = number_format($priceEnd,$tickSize);
            $objPrices->way = ($objPrices->end < $objPrices->start) ? "down" : "up";
            $objPrices->dateStart = $dateStart;
            $objPrices->dateEnd = $dateEnd;

            return $objPrices;

        }

        /**
         * --- MONTH
         */

        public function getDataMonth($symbol, $tickSize) {

            $startTime=strtotime(date('Y-m-d', strtotime('today - 30 days')))*1000;

            $pathCandles="https://www.okex.com/api/v1/kline.do?symbol=${symbol}&type=1hour&since=$startTime&size=720";
            $fileCandles=@file_get_contents($pathCandles);

            $arrayCandles=false;

            $arrayData = array();

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                $k=0;
                foreach($arrayCandles as $v) {
                    $arrayData[$k]["open"] = $v[1];
                    $arrayData[$k]["close"] = $v[4];
                    $arrayData[$k]["timestamp"] = date("Y-m-d H:i:s", $v[0]/1000);
                    $k++;
                }

            }

            return $arrayData;

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

            //$startTime=strtotime(date("Y-m-d 00:00:00"))*1000;
            $startTime=strtotime(date('Y-m-d', strtotime('-24 hours')))*1000;

            $pathCandles="https://www.okex.com/api/v1/kline.do?symbol=${symbol}&type=15min&since=$startTime&size=96";
            $fileCandles=file_get_contents($pathCandles);

            var_dump($fileCandles);

            $arrayCandles=false;

            $arrayData = array();

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                $k=0;
                foreach($arrayCandles as $v) {
                    $arrayData[$k]["open"] = $v[1];
                    $arrayData[$k]["close"] = $v[4];
                    $arrayData[$k]["timestamp"] = date("Y-m-d H:i:s", $v[0]/1000);
                    $k++;
                }

            }

            return $arrayData;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            $k=0;

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

                break;

            }

            var_dump($arrayPrices);

            return $arrayPrices;

        }

    }

?>