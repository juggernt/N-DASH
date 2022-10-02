<?php $NDASH = array (
  'currentversion' => '2.2',
  'nlgrate' => 
	  array (
	    '0' => array (
		    'exchange' => 'Stex',
		    'market' => 'https://api3.stex.com/public/ticker/1248',
		    'data' => 'data',
		    'link' => 'fiatsRate->EUR',
		    'symbol' => '&euro;',
		    'rounding' => 2,
		  ),
	    '1' => array (
		    'exchange' => 'CoinGecko',
		    'market' => 'https://api.coingecko.com/api/v3/simple/price?ids=florin&vs_currencies=eur',
		    'link' => 'florin->eur',
		    'symbol' => '&euro;',
		    'rounding' => 2,
		  ),
      ),
  );
?>
