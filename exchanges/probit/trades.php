<?php

    class trades implements koinsTrades {

        public function __construct($koin) {

            $this->koin = $koin;

            $this->uri_websocket = "wss://api.probit.com/api/exchange/v1/ws";

            $this->_wsConnect();

        }

        public function _wsConnect() {

            require_once(BASE_PATH."/scripts/resync/libs/websocket/autoload.php");
            $this->wsclient = new \WebSocket\Client($this->uri_websocket);

        }

        public function _getData($symbol, $websocket=true) {

            $arraySocketData = array();

            if ($websocket) {

                $arrayRequest = array();
                $arrayRequest["type"] = "subscribe";
                $arrayRequest["channel"] = "marketdata";
                $arrayRequest["interval"] = 100;
                $arrayRequest["market_id"] = $symbol;
                $arrayRequest["filter"][0] = "recent_trades";

                $strRequest = json_encode($arrayRequest);

                $this->wsclient->send($strRequest);

                $arraySocketData = json_decode($this->wsclient->receive(), true);

            } else {

                $dateStart = date("Y-m-d H:i:s");

                $dateStart = date("Y-m-d H:i:s", strtotime($dateStart . ' - 30 minutes'));

                $today = date("Y-m-d", strtotime($dateStart));
                $hour = date("H:i:s", strtotime($dateStart));

                $date_start = $today . "T" . $hour;

                $w_date_end = date('Y-m-d H:i:s');

                $w_date_end_today = date("Y-m-d", strtotime($w_date_end));
                $w_date_end_hour = date("H:i:s", strtotime($w_date_end));

                $date_end = $w_date_end_today . "T" . $w_date_end_hour;

                $uriPath = "https://api.probit.com/api/exchange/v1/trade?market_id={$symbol}&start_time={$date_start}.000Z&end_time={$date_end}.000Z&limit=10000";
                $arrayData = json_decode(file_get_contents($uriPath), true);
                $arraySocketData = $arrayData["data"];

            }

            return $arraySocketData;

        }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getTrades($arraySymbols, $exchange) {

            // files
            $dirBase = BASE_PATH."/data/resync";
            $dirBaseExchange = BASE_PATH."/data/resync/$exchange";
            $dirBaseIds = BASE_PATH."/data/resync/$exchange/ids";
            $dirBaseData = BASE_PATH."/data/resync/data";

            if (!is_dir($dirBase)) {
                mkdir($dirBase, 0777);
            }

            if (!is_dir($dirBaseExchange)) {
                mkdir($dirBaseExchange, 0777);
            }

            if (!is_dir($dirBaseIds)) {
                mkdir($dirBaseIds, 0777);
            }

            if (!is_dir($dirBaseData)) {
                mkdir($dirBaseData, 0777);
            }

            $arrayIds = array();

            $totalRequests=0;

            $arrayTrades = array();

            foreach($arraySymbols as $k => $v) {

                $symbol = $arraySymbols[$k]["symbol"];
                $dirBaseIdsSymbol = "$dirBaseIds/$symbol";

                if (!is_dir($dirBaseIdsSymbol)) {
                    mkdir($dirBaseIdsSymbol, 0777);
                }

                $lockFile = $dirBaseIdsSymbol . "/lock.json";
                if (!file_exists($lockFile)) {

                    $arrayData = $this->_getData($symbol, false);

                    // var_dump($arrayData);
                    // exit;

                    // $arrayDataRequest = $this->_getData($symbol);
                    // $arrayData = $arrayDataRequest["recent_trades"];

                    $totalRequests++;

                    $totalBuy = 0;
                    $totalSell = 0;

                    if (!empty($arrayData)) {

                        foreach($arrayData as $k2 => $v2) {

                            if (!empty($v2["time"])) {

                                $timestamp = date("Ymd",strtotime($v2["time"]));

                                // load id
                                if (empty($arrayIds[$timestamp])) {
                                    if (file_exists($dirBaseIdsSymbol . "/$timestamp.json")) {
                                        $arrayIds[$timestamp] = json_decode(file_get_contents($dirBaseIdsSymbol . "/$timestamp.json"),true);
                                    } else {
                                        $arrayIds[$timestamp] = array();
                                    }
                                }

                                if (!in_array($v2["id"], $arrayIds[$timestamp])) {

                                    if ($v2["side"] == "buy") {
                                        $totalBuy = ($v2["quantity"]+$totalBuy);
                                    } else {
                                        $totalSell = ($v2["quantity"]+$totalSell);
                                    }

                                    $arrayIds[$timestamp][] = $v2["id"];

                                }

                            }

                        }

                        // save ids
                        if (!empty($arrayIds)) {

                            foreach($arrayIds as $kda => $vda) {

                                $fileIds = $dirBaseIdsSymbol . "/$kda.json";
                                file_put_contents($fileIds, json_encode($vda));

                            }

                        }

                        $arrayTrades[$k] = new stdClass;
                        $arrayTrades[$k]->symbol = $symbol;

                        $arrayTrades[$k]->totalSell = new stdClass;
                        $arrayTrades[$k]->totalSell = $totalSell;

                        $arrayTrades[$k]->totalBuy = new stdClass;
                        $arrayTrades[$k]->totalBuy = $totalBuy;

                        if (!empty($arrayData[0]["time"])) {
                            $arrayTrades[$k]->timestamp = date("Y-m-d H:i:s", strtotime($arrayData[0]["time"]));
                        }

                        // lock file
                        if (file_exists($lockFile) && !empty($arrayTrades[$k]->totalBuy) && !empty($arrayTrades[$k]->totalSell)) {
                            unlink($lockFile);
                        }

                        if (empty($arrayTrades[$k]->totalBuy) || empty($arrayTrades[$k]->totalSell)) {
                            file_put_contents($lockFile, json_encode(array('lock'=>true)));
                        }

                    }

                } else {
                    unlink($lockFile);
                }

            }

            return $arrayTrades;

        }

    }

?>