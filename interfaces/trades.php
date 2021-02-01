<?php

    /**
     * Interface for currency
     * @author José Wilker <jose.wilker@smartapps.com.br>
     */

    interface koinsTrades {

        public function __construct($koin);

        public function getTrades($arraySymbols, $exchange);

    }

?>