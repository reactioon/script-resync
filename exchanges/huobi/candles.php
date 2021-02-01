<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $limit = 20;

            $pathCandles = "https://api.huobipro.com/market/history/kline?period=30min&size=$limit&symbol=$symbol";

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            $arrayReturn = array();

            if (!empty($arrayCandles)) {

                foreach($arrayCandles["data"] as $k => $v) {

                    if ($k == $limit) {
                        break;
                    }

                    $dt = date("Y-m-d H:i", $v["id"]);
                    $ds = date("Y-m-d H:i", strtotime($dt . " +3 hours"));

                    $arrayReturn[$k]["open"] = $v["open"];
                    $arrayReturn[$k]["high"] = $v["high"];
                    $arrayReturn[$k]["low"] = $v["low"];
                    $arrayReturn[$k]["close"] = $v["close"];
                    $arrayReturn[$k]["volume"] = $v["vol"];
                    $arrayReturn[$k]["date_start"] = $ds;
                    $arrayReturn[$k]["date_end"] = date("Y-m-d H:i", strtotime($ds . " +30 minutes"));

                }

            }

            return $arrayReturn;

        }

        /**
         * get candles of a symbol
         * @param  [type] $symbol [description]
         * @return [type]         [description]
         */


        public function getPrices($symbol, $candles, $start, $end, $tickSize=8) {

            if (empty($candles[0]["open"])) { return false; }

            $priceStart = $candles[0]["open"];
            $priceEnd = $candles[count($candles)-1]["close"];

            $dateStart = $start;
            $dateEnd = $end;

            $objPrices = new stdClass;
            $objPrices->symbol = $symbol;
            $objPrices->start = number_format($priceStart,$tickSize, ".", "");
            $objPrices->end = number_format($priceEnd,$tickSize, ".", "");
            $objPrices->way = ($objPrices->end < $objPrices->start) ? "down" : "up";
            $objPrices->dateStart = date("Y-m-d H:i:s", $dateStart);
            $objPrices->dateEnd = date("Y-m-d H:i:s", $dateEnd);

            return $objPrices;

        }

        /**
         * --- MONTH
         */

        public function getDataMonth($symbol, $tickSize) {

            $pathCandles = "https://api.huobipro.com/market/history/kline?period=60min&size=1440&symbol=$symbol";
            $arrayCandles = $this->koin->requestJSON($pathCandles);

            if (!empty($arrayCandles["data"])) {
                return $arrayCandles["data"];
            }

            return false;

        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            $startTime=strtotime(date('Y-m-d', strtotime('today - 30 days')));
            $endTime=strtotime(date("Y-m-d 23:59:59"));

            $k = 0;

            // construct data
            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataMonth($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, $startTime, $endTime, $arraySymbols[$i]["tickSize"]);

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

            $pathCandles = "https://api.huobipro.com/market/history/kline?period=30min&size=48&symbol=$symbol";
            $arrayCandles = $this->koin->requestJSON($pathCandles); //@file_get_contents($pathCandles);

            if (!empty($arrayCandles["data"])) {
                return $arrayCandles["data"];
            }

            return false;

        }

        public function getDay($bin, $arraySymbols, $exchange) {

            $starttime = microtime(true);

            $arrayPrices = array();

            $startTime=strtotime(date("Y-m-d 00:00:00"));
            $endTime=strtotime(date("Y-m-d 23:59:59"));

            $k=0;

            for($i=0; $i < count($arraySymbols); $i++) {

                $symbol = $arraySymbols[$i]["symbol"];

                $candles = $this->getDataDay($symbol, $arraySymbols[$i]["tickSize"]);

                $prices = $this->getPrices($symbol, $candles, $startTime, $endTime, $arraySymbols[$i]["tickSize"]);

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