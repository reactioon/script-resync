<?php

    // if (!function_exists('sortFunctionCandlesMercadoBitcoin')) {
    //     function sortFunctionCandlesMercadoBitcoin( $a, $b ) {
    //         return strtotime($a["datetime"]) - strtotime($b["datetime"]);
    //     }
    // }

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $date = date("Y-m-d");

            $days = 30;

            $asset = str_replace("BRL","", $symbol);

            $arrayData = array();

            $strdate = $date . " -1 days";
            $ndate = date('Y/m/d', strtotime($strdate));

            $pathCandles = "https://www.mercadobitcoin.net/api/$asset/day-summary/$ndate";

            $fileCandles=@file_get_contents($pathCandles);

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                $k = 0;

                $arrayData[$k]["open"] = number_format($arrayCandles["opening"],3,".","");
                $arrayData[$k]["high"] = number_format($arrayCandles["highest"],3,".","");
                $arrayData[$k]["low"] = number_format($arrayCandles["lowest"],3,".","");
                $arrayData[$k]["close"] = number_format($arrayCandles["closing"],3,".","");
                $arrayData[$k]["volume"] = number_format($arrayCandles["volume"],3,".","");
                $arrayData[$k]["date_start"] = date("Y-m-d 00:00", strtotime($strdate));
                $arrayData[$k]["date_end"] = date("Y-m-d 23:59", strtotime($strdate));

            }

            // usort($arrayData, "sortFunctionCandlesMercadoBitcoin");

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

            $date = date("Y-m-d");

            $asset = str_replace("BRL","", $symbol);

            $arrayData = array();

            for($i=1; $i < 30; $i++) {

                $strdate = $date . " -$i days";
                $ndate = date('Y/m/d', strtotime($strdate));

                $pathCandles = "https://www.mercadobitcoin.net/api/$asset/day-summary/$ndate";

                $arrayCandles = $this->koin->requestJSON($pathCandles); // @file_get_contents($pathCandles);

                if (!empty($arrayCandles)) {

                    // $arrayCandles=json_decode($fileCandles, true);

                    $k = $i-1;

                    $arrayData[$k]["open"] = $arrayCandles["opening"];
                    $arrayData[$k]["close"] = $arrayCandles["closing"];
                    $arrayData[$k]["timestamp"] = $arrayCandles["date"];

                }

                sleep(1);

            }

            return $arrayData;
        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            $k = 0;

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

            $date = date("Y-m-d");

            $strdate = $date . " -1 days";
            $date = date('Y/m/d', strtotime($strdate));

            $asset = str_replace("BRL","", $symbol);

            $pathCandles = "https://www.mercadobitcoin.net/api/$asset/day-summary/$date";

            $arrayCandles = $this->koin->requestJSON($pathCandles); //@file_get_contents($pathCandles);

            // $arrayCandles=false;

            $arrayData = array();

            if (!empty($arrayCandles)) {

                // $arrayCandles=json_decode($fileCandles, true);

                $arrayData[0]["open"] = $arrayCandles['opening'];
                $arrayData[0]["close"] = $arrayCandles['closing'];
                $arrayData[0]["timestamp"] = $arrayCandles["date"];

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

            }

            return $arrayPrices;

        }

    }

?>
