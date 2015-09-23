<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Zend\Json\Expr;

/**
 * Class AnalyseController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/analyse")
 */
class AnalyseController extends Controller
{
    /**
     * Uri list of Analyse statistics
     *
     * @Route("/",name="dwd_csadmin_analyse_dashboard")
     */
    public function indexAction(Request $request)
    {
        $date = $request->get('date');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $mongoConn = $dm->getConnection()->getMongo()->selectDB('iqianggou_analyse');
        $oal = $mongoConn->selectCollection('openapi_access_data');

        if (isset($date) && !empty($date)) {
            $startTimestamp = strtotime($date);
        } else {
            $lastRecord = $oal->find()->sort(['startTimestamp' => -1])->limit(1)->getNext();
            $startTimestamp = $lastRecord['startTimestamp'];
        }

        $apiDataCursor = $oal->find([
            'startTimestamp' => $startTimestamp,
        ])->sort(['called' => -1]);
        $apiList = iterator_to_array($apiDataCursor);

//        $response = new Response();
//        $response->setContent(json_encode($apiList));
//        return $response;

        return $this->render('DWDCSAdminBundle:Analyse:api_list.html.twig', array(
            'apiList'       => $apiList,
            'title'         => date('Y-m-d', $startTimestamp) . ' API访问'
        ));
    }

    /**
     * Uri chart of Analyse statistics
     *
     * @Route("/urichart",name="dwd_csadmin_analyse_dashboard_urichart")
     */
    public function showUriChartAction(Request $request)
    {
        $uri = $request->get('uri');
        $startTime = $request->get('startTime');
        $duration = $request->get('duration');
        $type = $request->get('type');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $mongoConn = $dm->getConnection()->getMongo()->selectDB('iqianggou_analyse');

        $calledArray = array();
        $costArray = array();
        $categories = array();

        if ($type == 'day') {
            $oal = $mongoConn->selectCollection('openapi_access_data');
            $apiCursor = $oal->find([
               'path' => $uri
            ])->sort(['startTimestamp' => 1]);
            foreach ($apiCursor as $doc) {
                if ($doc['totalCost'] < 0) {
                    continue;
                }
                $calledArray []= $doc['called'];
                $costArray []= round($doc['totalCost'] / $doc['called'], 2);
                $categories []= date("Y-m-d", $doc['startTimestamp']);
            }
        } else {
            $oal = $mongoConn->selectCollection('openapi_access_logs');
            $startTimestamp = intval($startTime);

            for ( $i = 0; $i < 12; $i ++ ) {
                $requestCursor = $oal->find([
                    'request_time' => [
                        '$gte' => $startTimestamp,
                        '$lt' => $startTimestamp + $duration
                    ],
                    'path' => $uri
                ]);
                $startTimestamp += $duration;

                $called = 0;
                $totalCost = 0;
                foreach ($requestCursor as $doc) {
                    if( $doc['cost'] < 0 ) {
                        continue;
                    }
                    $called ++;
                    $totalCost += $doc['cost'];
                }
                $calledArray []= $called;
                if( $called ) {
                    $costArray []= $totalCost / $called;
                } else {
                    $calledArray []= 0;
                }
            }
            $categories = array('0-5', '5-10', '10-15', '15-20', '20-25', '25-30', '30-35', '35-40', '40-45', '45-50', '50-55', '55-60');
        }

        $series = array(
            array(
                'name'  => '访问次数',
                'type'  => 'column',
                'color' => '#4572A7',
                'yAxis' => 1,
                'data'  => $calledArray,
            ),
            array(
                'name'  => '平均响应时间',
                'type'  => 'spline',
                'color' => '#AA4643',
                'data'  => $costArray,
            ),
        );
        $yData = array(
            array(
                'labels' => array(
                    'formatter' => new Expr('function () { return this.value + " ms" }'),
                    'style'     => array('color' => '#AA4643')
                ),
                'title' => array(
                    'text'  => '平均响应时间',
                    'style' => array('color' => '#AA4643')
                ),
                'opposite' => true,
            ),
            array(
                'labels' => array(
                    'formatter' => new Expr('function () { return this.value }'),
                    'style'     => array('color' => '#4572A7')
                ),
                'gridLineWidth' => 0,
                'title' => array(
                    'text'  => '访问次数',
                    'style' => array('color' => '#4572A7')
                ),
            ),
        );

        $ob = new Highchart();
        $ob->chart->renderTo('container'); // The #id of the div where to render the chart
        $ob->chart->type('column');
        $ob->title->text('访问请求-' . $uri);
        $ob->xAxis->categories($categories);
        $ob->yAxis($yData);
        $ob->legend->enabled(false);
        $formatter = new Expr('function () {
                 var unit = {
                     "访问次数": "次",
                     "平均响应时间": "ms"
                 }[this.series.name];
                 return this.x + ": <b>" + this.y + "</b> " + unit;
             }');
        $ob->tooltip->formatter($formatter);
        $ob->series($series);

        return $this->render('DWDCSAdminBundle:Analyse:url_chart.html.twig', array(
            'chart'        => $ob
        ));
    }
}