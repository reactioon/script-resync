<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {

            $this->koin = $koin;

            $this->uri_websocket = "wss://api.probit.com/api/exchange/v1/ws";

            $this->_wsConnect();

        }

        public function _wsConnect() {

            require_once(BASE_PATH."/scripts/resync/libs/websocket/autoload.php");
            $this->wsclient = new \WebSocket\Client($this->uri_websocket, array('timeout' => 900));

        }

        public function _getDataTicker($symbol) {

            if (isset($this->arrayDataTickers[$symbol])) {
                return $this->arrayDataTickers[$symbol];
            }

            return false;

        }

        public function _getOrderBook($symbol) {

            $uri = "https://api.probit.com/api/exchange/v1/order_book?market_id=$symbol";
            $orderbook = $this->koin->requestJSON($uri); // json_decode(@file_get_contents("https://api.probit.com/api/exchange/v1/order_book?market_id=$symbol"), true);

            if (!empty($orderbook["data"])) {
                return $orderbook["data"];
            }

            return false;

        }

        public function _getData() {

            $uriTicker = "https://api.probit.com/api/exchange/v1/ticker";
            $fileTickers = $this->koin->requestJSON($uriTicker); // json_decode(@file_get_contents("https://api.probit.com/api/exchange/v1/ticker"), true);

            $arrayDataTickers = array();

            if (!empty($fileTickers["data"])) {
                foreach($fileTickers["data"] as $k => $v) {
                    $arrayDataTickers[$v["market_id"]] = $v;
                }
            }

            $this->arrayDataTickers = $arrayDataTickers;

        }

        public function _getPrice($arrayBook, $type) {

            if (!empty($arrayBook)) {

                $bookReverse = array_reverse($arrayBook);

                $price = false;

                foreach($bookReverse as $k => $v) {
                    if ($v['side'] == $type) {
                        $price = $v["price"];
                        break;
                    }
                }

                if (!$price) {
                    return false;
                }

                return $price;

            }

        }

        // public function getBySymbol($symbol) {

        //     $pathTicker = "https://api.probit.com/api/exchange/v1/ticker?market_ids=$symbol";
        //     $fileTicker = file_get_contents($pathTicker);

        //     $arrayTicker = array();
        //     if (!empty($fileTicker)) {

        //         // get ticker
        //         $ticker = json_decode($fileTicker, true);
        //         $arrayRequestTicker = $ticker["data"][0];

        //         // var_dump($arrayRequestTicker);

        //         $arrayTicker["last"] = $arrayRequestTicker['last'];
        //         $arrayTicker["low"] = $arrayRequestTicker['low'];
        //         $arrayTicker["high"] = $arrayRequestTicker['high'];
        //         $arrayTicker["base_volume"] = $arrayRequestTicker['base_volume'];
        //         $arrayTicker["quote_volume"] = $arrayRequestTicker['quote_volume'];

        //     }

        //     return $arrayTicker;

        // }

        /**
         * Get all tickers
         * @return [type] [description]
         */
        public function getTickers($arraySymbols, $exchange, $pair) {

            // files
            $dirBase = BASE_PATH."/data/resync";
            $dirBaseData = BASE_PATH."/data/resync/data";

            if (!is_dir($dirBase)) {
                mkdir($dirBase, 0777);
            }

            if (!is_dir($dirBaseData)) {
                mkdir($dirBaseData, 0777);
            }

            $starttime = microtime(true);

            $arrayTickers = array();

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $stringRequest = "";
                $totalSymbols = count($arraySymbols);

                $data = $this->_getData();

                $t = 0;

                foreach($arraySymbols as $k => $v) {

                    $symbol = $v["symbol"];

                    $dataTicker = $this->_getDataTicker($v["symbol"]);

                    if ($dataTicker) {

                        $dataBook = $this->_getOrderBook($v["symbol"]);

                        $arrayTicker = $dataTicker;
                        $arrayBook = $dataBook;

                        $priceBuy = $this->_getPrice($arrayBook, "buy");
                        $priceSell = $this->_getPrice($arrayBook, "sell");

                        $arrayReturn[$t]["symbol"] = $symbol;
                        $arrayReturn[$t]["ask"] = $priceSell;
                        $arrayReturn[$t]["bid"] = $priceBuy;
                        $arrayReturn[$t]["low"] = $arrayTicker["low"];
                        $arrayReturn[$t]["high"] = $arrayTicker["high"];
                        $arrayReturn[$t]["last"] = $arrayTicker["last"];
                        $arrayReturn[$t]["volume"] = $arrayTicker["base_volume"];

                        $t++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>