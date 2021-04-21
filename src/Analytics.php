<?php

namespace Necessarylion;

class Analytics {

  /**
   * @var string|int $viewId
   */
  protected $viewId;

  /**
   * @var Google_Service_Analytics $analytics
   */
  protected $analytics;

  /**
   * @var string $credentialPath
   */
  protected $credentialPath;

  /**
   * @var Carbon $startDate
   */
  protected $startDate;

  /**
   * @var Carbon $endDate
   */
  protected $endDate;

  /**
   * this function will call inside init
   */
  protected function _getClient() {
    $client = new \Google_Client();
    $client->setAuthConfig($this->credentialPath);
    $client->setScopes(\Google_Service_Analytics::ANALYTICS_READONLY);
    return $client;
  }

  /**
   * @param string $path
   * @return this
   */
  public function setCredentialPath($path) {
    $this->credentialPath = $path;
    return $this;
  }

  /**
   * @param string $viewId
   * @return this
   */
  public function setViewId($viewId) {
    $this->viewId = $viewId;
    return $this;
  }

  /**
   * initiator
   * @return this
   */
  public function init() {
    if(!$this->credentialPath) {
      throw new Exception('set credential path first using setCredentialPath($path)');
    }
    $this->analytics = new \Google_Service_Analytics($this->_getClient());
    return $this;
  }
  
  /**
   * set starting date
   * @param Carbon $date
   * @return this
   */
  public function setStartDate($date) {
    $this->startDate = $date;
    return $this;
  }

  /**
   * set ending date
   * @param Carbon $date
   * @return this
   */
  public function setEndDate($date) {
    $this->endDate = $date;
    return $this;
  }

  /**
   * this function will call google api and return response
   * @param string $metrics "comma separated string"
   * @param array $others eg - dimensions, sort, max-result
   * @return this
   */
  public function runQuery($metrics, $others = []) {
    $others['max-results'] = 20;
    $result                = $this->analytics->data_ga->get(
      "ga:{$this->viewId}",
      $this->startDate->format('Y-m-d'),
      $this->endDate->format('Y-m-d'),
      $metrics,
      $others
    );
    $this->response = $result['rows'] ?? [];
    return $this;
  }

  /**
   * this function will format final result
   * @param array $columns "name fo columns"
   * eg- [date, visitors, pageViews]
   * @return array
   */
  public function getResult($columns) {
    $ret = [];
    foreach($this->response as $response) {
      $temp = [];
      foreach($columns as $key => $value) {
        $temp[$value] = ($value == 'date') ? Carbon::createFromFormat('Ymd',$response[$key]) : $response[$key];
      }
      $ret[] = $temp;
    }
    return $ret;
  }

  /**
   * to get raw data response;
   * @return array
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * get total visitors and page views
   * @return array
   */
  public function getTotalVisitorAndPageView() {
    $this->runQuery('ga:users,ga:pageviews', ['dimensions' => 'ga:date']);
    return $this->getResult(['date', 'visitors', 'pageViews']);
  }

  /**
   * get most visited pages
   * @return array
   */
  public function getMostVisitedPages() {
    $this->runQuery(
      'ga:pageviews',
      [
        'dimensions' => 'ga:pagePath,ga:pageTitle',
        'sort'       => '-ga:pageviews',
      ]
    );
    return $this->getResult(['url', 'pageTitle', 'pageViews']);
  }

  /**
   * get user types
   * @return array
   */
  public function getUserTypes() {
    $this->runQuery(
      'ga:sessions',
      [
        'dimensions' => 'ga:userType',
      ]
    );
    return $this->getResult(['type', 'sessions']);
  }

  /**
   * get top Referrers links
   * @return array
   */
  public function getTopReferrers() {
    $this->runQuery(
      'ga:pageviews',
      [
        'dimensions' => 'ga:fullReferrer',
        'sort'       => '-ga:pageviews',
      ]
    );
    return $this->getResult(['url', 'pageViews']);
  }

}