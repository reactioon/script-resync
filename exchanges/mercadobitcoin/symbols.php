<?php

    class symbols implements koinsSymbols {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getAll($currency) {

            $arraySymbols = array(
                0 => array(
                    'symbol' => 'BRLBTC',
                    'increment' => '0.001',
                    'ticksize' => 3
                ),
                1 => array(
                    'symbol' => 'BRLLTC',
                    'increment' => '0.01',
                    'ticksize' => 2
                ),
                2 => array(
                    'symbol' => 'BRLBCH',
                    'increment' => '0.001',
                    'ticksize' => 3
                ),
                3 => array(
                    'symbol' => 'BRLXRP',
                    'increment' => '0.1',
                    'ticksize' => 1
                ),
                4 => array(
                    'symbol' => 'BRLETH',
                    'increment' => '0.01',
                    'ticksize' => 2
                )
            );

            $arrayReturn = array();

            foreach($arraySymbols as $k => $v) {

                $arrayReturn[$k]["currency"] = str_replace("BRL","", $v["symbol"]);
                $arrayReturn[$k]["pair"] = 'BRL';
                $arrayReturn[$k]["symbol"] = $v['symbol'];
                $arrayReturn[$k]["tickSize"] = $v['ticksize'];
                $arrayReturn[$k]["type"] = $currency;
                $arrayReturn[$k]["quantityIncrement"] = $v['increment'];
                $arrayReturn[$k]["quantityMinimal"] = 50*2;

            }

            return $arrayReturn;

        }

    }

?>