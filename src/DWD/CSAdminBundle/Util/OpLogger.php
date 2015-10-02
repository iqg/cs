<?php
/**
 * Created by PhpStorm.
 * User: caowei
 * Date: 8/24/15
 * Time: 17:48
 */

namespace DWD\CSAdminBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use DWD\DataBundle\Document\Oplog;
 
class OpLogger
{
	 public function __construct(Container $container)
     {
        $this->container = $container;
     }

	 public function addCommonLog( $params )
     {
     	 $opLog                = new Oplog();
     	 
     	 $opLog->setAdminId( $params['adminId'] );
     	 $opLog->setRoute( $params['route'] );
     	 $opLog->setRes( $params['res'] );
     	 $opLog->setExt( json_encode( $params['ext'] ) );
     	 $opLog->setCratedAt( time() );
     	 $dm                   = $this->container->get('doctrine_mongodb')->getManager();
         $dm->persist($opLog);
         $dm->flush();

         return $opLog;
     }
}