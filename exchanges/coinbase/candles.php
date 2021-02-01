<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $limit = 1;

            $pathCandles="https://api.gdax.com/products/$symbol/candles?granularity=3600";

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayReturn = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles as $k => $v) {

                    if ($k == $limit) {
                        break;
                    }

                    $ds = date("Y-m-d H:i", $v[0]);
                    $ds = date("Y-m-d H:i", strtotime($ds . " +3 hour"));

                    $arrayReturn[$k]["open"] = $v[3];
                    $arrayReturn[$k]["high"] = $v[2];
                    $arrayReturn[$k]["low"] = $v[1];
                    $arrayReturn[$k]["close"] = $v[4];
                    $arrayReturn[$k]["volume"] = $v[5];
                    $arrayReturn[$k]["date_start"] = $ds;
                    $arrayReturn[$k]["date_end"] = date("Y-m-d H:i", strtotime($ds . "+30 minutes"));

                }

            }

            return $arrayReturn;

        }

        public function getPrices($symbol, $candles, $start, $end=false, $tickSize=8) {

            if (empty($candles[$start]["open"])) { return false; }

            $priceStart = $candles[count($candles)-1]["open"];
            $priceEnd = (!$end) ? $candles[$start]["close"] : $candles[$start]["close"];

            $dateStart = $candles[count($candles)-1]["timestamp"];
            $dateEnd = (!$end) ? $candles[$start]["timestamp"] : $candles[$start]["timestamp"];

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

            $endTime=date("Y-m-d%20H:i:s");
            $startTime=date('Y-m-d%20H:i:s',strtotime('-30 days'));

            $pathCandles="https://api.gdax.com/products/$symbol/candles?start=$startTime&end=$endTime&granularity=21600";
            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayReturn = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles as $k => $v) {
                    $arrayReturn[$k]["open"] = $v[3];
                    $arrayReturn[$k]["close"] = $v[4];
                    $arrayReturn[$k]["timestamp"] = date("Y-m-d H:i:s", $v[0]);
                }
            }

            return $arrayReturn;

        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

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


            if (empty($arrayPrices)) {
                $arrayPrices = $this->getMonth($bin, $arraySymbols, $exchange);
            }

            return $arrayPrices;

        }

        /**
         * --- DAY
         */

        public function getDataDay($symbol, $tickSize) {

            $endTime=date("Y-m-d%20H:i:s");
            $startTime=date('Y-m-d%20H:i:s',strtotime('-24 hours'));

            $pathCandles="https://api.gdax.com/products/$symbol/candles?start=$startTime&end=$endTime&granularity=3600";

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayReturn = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles as $k => $v) {
                    $arrayReturn[$k]["open"] = $v[3];
                    $arrayReturn[$k]["close"] = $v[4];
                    $arrayReturn[$k]["timestamp"] = date("Y-m-d H:i:s", $v[0]);
                }
            }

            return $arrayReturn;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            $k = 0;

            // var_dump(count($arraySymbols));

            // construct data
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

                // var_dump($arrayPrices);
                // var_dump($symbol);

                // break;

            }

            // if (empty($arrayPrices)) {
            //     $arrayPrices = $this->getDay($bin, $arraySymbols, $exchange);
            // }

            return $arrayPrices;

        }

    }

?>