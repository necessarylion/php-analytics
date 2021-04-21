# PHP Google Analytics

`composer require necessarylion/php-analytics`

```

<?php

use Carbon\Carbon;
use Necessarylion\Analytics;


require __DIR__."/vendor/autoload.php";

$analytics = new Analytics;
$analytics->setViewId('XXXXXXXXX');
$analytics->setCredentialPath(__DIR__ . "/analytics.json");
$analytics->init();

$analytics->setStartDate(Carbon::now());
$analytics->setEndDate(Carbon::now());

// get top referrers links
$analytics->getTopReferrers();

// get total visitors and total page view
$analytics->getTotalVisitorAndPageView();

// get visitor type with total views eg. New Visitor, Returning Visitor 
$analytics->getUserTypes();

// get top 20 most visited pages
$analytics->getMostVisitedPages();


// run custom query
$analytics->runQuery('ga:users,ga:sessions,ga:bounceRate,ga:avgSessionDuration');
$result = $analytics->getResult(['users', 'sessions', 'bounceRate', 'sessionDuration']);

print_r($result);

// get raw response
$result = $analytics->getResponse();

```