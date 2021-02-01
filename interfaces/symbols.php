<?php

    /**
     * Interface for currency
     * @author José Wilker <jose.wilker@smartapps.com.br>
     */

    interface koinsSymbols {

        public function __construct($koin);

        // get all symbols
        public function getAll($type);

    }

?>