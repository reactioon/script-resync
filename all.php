<?php

    require_once("libs/koins.php");

    class script_all {

        public function __construct($__bare, $args) {

            $this->__bare = $__bare;

            $sargs = false;
            parse_str($args, $sargs);

            $this->args = $sargs;

            $this->init();

        }

        public function init() {

            $currency = (!empty($this->args["currency"])) ? $this->args["currency"] : false;
            $exchange = (!empty($this->args["exchange"])) ? $this->args["exchange"] : false;
            $action = (!empty($this->args["action"])) ? $this->args["action"] : false;
            $hash = (!empty($this->args["hash"])) ? $this->args["hash"] : false;
            $returnFormat = (!empty($this->args["returnFormat"])) ? $this->args["returnFormat"] : "temp";
            $expire = (!empty($this->args["expire"])) ? $this->args["expire"] : false;

            if (empty($currency)) {
                exit("Don't found a currency.");
            }

            if (empty($exchange)) {
                exit("Don't found an exchange.");
            }

            if (empty($action)) {
                exit("Don't found an action.");
            }

            if (empty($expire)) {
                exit("Don't found an expire time.");
            }

            $koin = new koins();

            // var_dump($currency);
            // var_dump($exchange);
            // var_dump($action);
            //
            // exit;

            // $key = "candles-day";
            // $exchange = "binance";
            // $pairs = array('BTC','USDT','TUSD');

            $start = $koin->start($action, $exchange, $currency, $hash, $returnFormat, $expire);

            if ($start) {

                $koin->run($start, $currency, $action);
                $koin->end($start);

            }

        }

    }

?>
