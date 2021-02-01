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

            $startTime=strtotime(date('Y-m-d', strtotime('today - 30 days')));
            $endTime=strtotime(date("Y-m-d 23:59:59"));

            $pathCandles="https://poloniex.com/public?command=returnChartData&currencyPair=${symbol}&start=${startTime}&end=${endTime}&period=1800";
            $arrayCandles=$this->koin->requestJSON($pathCandles);

            $arrayData = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles as $k => $v) {
                    $arrayData[$k]["open"] = number_format($v["open"], $tickSize);
                    $arrayData[$k]["close"] = number_format($v["close"], $tickSize);
                    $arrayData[$k]["timestamp"] = date("Y-m-d H:i:s", $v["date"]);
                }

            }

            return $arrayData;

        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            // construct data
            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataMonth($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, 0, false, $arraySymbols[$i]["tickSize"]);

                if (!empty($prices)) {

                    $percentageDiff = @abs(number_format((1 - $prices->end / $prices->start) * 100, 2));

                    $prices->percentage = $percentageDiff;

                    $arrayPrices[$i] = new stdClass;

                    $arrayPrices[$i]->prices = new stdClass;
                    $arrayPrices[$i]->symbol = new stdClass;

                    $arrayPrices[$i]->exchange = $exchange;
                    $arrayPrices[$i]->type = "month";
                    $arrayPrices[$i]->prices = $prices;
                    $arrayPrices[$i]->symbol = $arraySymbols[$i];

                }

            }

            return $arrayPrices;

        }

        /**
         * --- DAY
         */

        public function getDataDay($symbol, $tickSize) {

            $startTime=strtotime(date("Y-m-d 00:00:00"));
            $endTime=strtotime(date("Y-m-d 23:59:59"));

            $pathCandles="https://poloniex.com/public?command=returnChartData&currencyPair=${symbol}&start=${startTime}&end=${endTime}&period=1800";
            $arrayCandles=$this->koin->requestJSON($pathCandles);

            $arrayData = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles as $k => $v) {
                    $arrayData[$k]["open"] = $v["open"];
                    $arrayData[$k]["close"] = $v["close"];
                    $arrayData[$k]["timestamp"] = date("Y-m-d H:i:s", $v["date"]);
                }

            }

            return $arrayData;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataDay($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, 0, false, $arraySymbols[$i]["tickSize"]);

                if (!empty($prices)) {

                    $percentageDiff = @abs(number_format((1 - $prices->end / $prices->start) * 100, 2));

                    $prices->percentage = $percentageDiff;

                    $arrayPrices[$i] = new stdClass;

                    $arrayPrices[$i]->prices = new stdClass;
                    $arrayPrices[$i]->symbol = new stdClass;

                    $arrayPrices[$i]->exchange = $exchange;
                    $arrayPrices[$i]->type = "day";
                    $arrayPrices[$i]->prices = $prices;
                    $arrayPrices[$i]->symbol = $arraySymbols[$i];

                }

            }

            return $arrayPrices;

        }

    }

?>