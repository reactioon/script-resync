<?php

    class candles implements koinsCandles {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getCandlesLastBySymbol($symbol) {

            $limit = 20;

            $sort = "desc";
            $interval = "30m";

            $now = date("Y-m-d H:i:s");
            $day = date("Y-m-d");
            $hour = date("23:59:59");

            $date_hour = $day . " " . $hour;

            $date_end = str_replace(" ", "T", $date_hour) . ".000Z";
            $date_start = str_replace(" ", "T", date("Y-m-d H:i:s", strtotime($now . " -3 hours"))) . ".000Z";

            $pathCandles="https://api.probit.com/api/exchange/v1/candle?market_ids=$symbol&limit=$limit&sort=$sort&interval=$interval&start_time={$date_start}&end_time={$date_end}";
            $fileCandles=file_get_contents($pathCandles);

            $arrayCandles=array();

            $arrayData=array();

            if (!empty($fileCandles)) {

                $arrayCandles=json_decode($fileCandles, true);

                foreach($arrayCandles["data"] as $k => $v) {

                    if (!empty($v["open"])) {

                        $arrayData[$k]["open"] = number_format($v["open"],3,".","");
                        $arrayData[$k]["high"] = number_format($v["high"],3,".","");
                        $arrayData[$k]["low"] = number_format($v["low"],3,".","");
                        $arrayData[$k]["close"] = number_format($v["close"],3,".","");
                        $arrayData[$k]["volume"] = number_format($v["base_volume"],3,".","");
                        $arrayData[$k]["date_start"] = date("Y-m-d H:i", strtotime($v["start_time"] . " +3 hours"));
                        $arrayData[$k]["date_end"] = date("Y-m-d H:i", strtotime($v["end_time"] . " +3 hours"));

                    }

                }

            }

            return $arrayData;

        }

        public function getPrices($symbol, $candles, $start=0, $end=false, $tickSize=8) {

            if (empty($candles[$start]["open"])) { return false; }

            $priceStart = $candles[$start]["open"];
            $priceEnd = (!$end) ? $candles[count($candles)-1]["close"] : $candles[$end]["close"];

            $dateStart = $candles[$start]["start_time"];
            $dateEnd = (!$end) ? $candles[count($candles)-1]["end_time"] : $candles[$end]["end_time"];

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

            $date_start = date("Y-m-d");
            $date_start = date("Y-m-d", strtotime($date_start . ' - 31 days'));
            $date_end = date('Y-m-d');

            $pathCandles="https://api.probit.com/api/exchange/v1/candle?market_ids=$symbol&limit=1000&sort=asc&interval=1D&start_time={$date_start}T00:00:00.000Z&end_time={$date_end}T00:00:00.000Z";
            // $fileCandles=@file_get_contents($pathCandles);
            $arrayCandles = $this->koin->requestJSON($pathCandles);

            // $arrayCandles=false;

            $arrayData = array();

            if (!empty($arrayCandles)) {

                // $arrayCandles=json_decode($fileCandles, true);

                $y=0;

                if (!empty($arrayCandles["data"])) {

                    foreach($arrayCandles["data"] as $k => $v) {

                        if (!empty($v["open"])) {

                            $arrayData[$y]["open"] = $v["open"];
                            $arrayData[$y]["close"] = $v["close"];
                            $arrayData[$y]["start_time"] = date("Y-m-d H:i:s", strtotime($v["start_time"]));
                            $arrayData[$y]["end_time"] = date("Y-m-d H:i:s", strtotime($v["end_time"]));

                            $y++;

                        }

                    }

                }

            }

            return $arrayData;
        }

        public function getMonth($bin, $arraySymbols, $exchange) {

            $arrayPrices = array();

            $starttime = microtime(true);

            $total = count($arraySymbols);

            $k = 0;
            // var_dump($total);
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

            $date = date("Y-m-d");

            $startTime = $date . "T00:00:00.000Z";
            $endTime = $date . "T23:59:00.590Z";

            $pathCandles="https://api.probit.com/api/exchange/v1/candle?market_ids=$symbol&limit=1000&sort=asc&interval=30m&start_time=$startTime&end_time=$endTime";

            // var_dump($pathCandles);

            // $fileCandles=@file_get_contents($pathCandles);

            // $arrayCandles=false;

            $arrayData = array();

            $arrayCandles = $this->koin->requestJSON($pathCandles);

            if (!empty($arrayCandles)) {

                // $arrayCandles=json_decode($fileCandles, true);
                $y=0;
                if (!empty($arrayCandles["data"])) {
                    foreach($arrayCandles["data"] as $k => $v) {
                        $arrayData[$y]["open"] = $v["open"];
                        $arrayData[$y]["close"] = $v["close"];
                        $arrayData[$y]["start_time"] = date("Y-m-d H:i:s", strtotime($v["start_time"]));
                        $arrayData[$y]["end_time"] = date("Y-m-d H:i:s", strtotime($v["end_time"]));
                        $y++;
                    }
                }

            }

            return $arrayData;

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