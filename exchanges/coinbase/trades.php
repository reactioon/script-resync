<?php

    class trades implements koinsTrades {

        public function __construct($koin) {
            $this->koin = $koin;
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

            $today = date("Ymd");

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

                    $uriPath = "https://api.gdax.com/products/$symbol/trades";
                    $arrayDataTick = $this->koin->requestJSON($uriPath);

                    $totalRequests++;

                    $totalBuy = 0;
                    $totalSell = 0;

                    if (!empty($arrayDataTick)) {

                        foreach($arrayDataTick as $k2 => $v2) {

                            $timestamp = date("Ymd",strtotime($v2["time"]));

                            // load id
                            if (empty($arrayIds[$timestamp])) {
                                if (file_exists($dirBaseIdsSymbol . "/$timestamp.json")) {
                                    $arrayIds[$timestamp] = json_decode(file_get_contents($dirBaseIdsSymbol . "/$timestamp.json"),true);
                                } else {
                                    $arrayIds[$timestamp] = array();
                                }
                            }

                            if (!in_array($v2["trade_id"], $arrayIds[$timestamp])) {

                                if ($v2["side"] == "buy") {
                                    $totalBuy = ($v2["size"]+$totalBuy);
                                } else {
                                    $totalSell = ($v2["size"]+$totalSell);
                                }

                                $arrayIds[$timestamp][] = $v2["trade_id"];

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

                        $arrayTrades[$k]->timestamp = date("Y-m-d H:i:s");

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