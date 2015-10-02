<?php

namespace DWD\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Overtrue\Pinyin\Pinyin;
use DWD\DataBundle\Document\Store;

class UpdateStorePinyinCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('data:update_store_pinyin')
            ->setDescription('Update store pinyin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->loadStore();
    }

    /**
     * 按照最新插入的branch_id，增量导入到MongoDB
     */
    protected function loadStore()
    {
        // 获取mongodb的最大branch_id
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $last_branch = $dm->createQueryBuilder('DWDDataBundle:Store')
            ->limit(1)
            ->sort('branch_id', 'DESC')
            ->getQuery()
            ->getSingleResult();
        $last_branch_id = 0;
        if (isset($last_branch)) {
            $last_branch_id = $last_branch->getBranchId();
        }

        // 获取MySQL生产库的增量门店列表
        $host = "10.0.0.10";
        $user = "root";
        $password = "123456";
        $db = "backup_app_db";
        $this->connectDB($host, $user, $password, $db);
        $query = "SELECT `id`,`name`,`zone_id`,`address`,`lng`,`lat`,`enabled` FROM `branch` WHERE id > $last_branch_id";
        $branch_list = $this->getResultByQuery( $query );

        // 将门店列表进行拼音处理，导入到MongoDB
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        Pinyin::set('accent', false);
        Pinyin::set('delimiter', '');

//        $count = 0;
        foreach ($branch_list as $branch_info) {
            $this->writeln('brach id is ' . $branch_info['id'] . '. name is ' . $branch_info['name']);
            $storeInfo = new Store();
            $storeInfo->setBranchId( intval($branch_info['id']) );
            $storeInfo->setName($branch_info['name']);
            $storeInfo->setZoneId( intval($branch_info['zone_id']) );
            $storeInfo->setAddress($branch_info['address']);
            $storeInfo->setLng($branch_info['lng']);
            $storeInfo->setLat($branch_info['lat']);
            $storeInfo->setEnabled( intval($branch_info['enabled']) );

            $pinyin_full = Pinyin::trans($branch_info['name']);
            $pinyin_full = preg_replace('~[^\p{Han}0-9a-zA-Z]~u', '', $pinyin_full);
            $pinyin_first = Pinyin::letter($branch_info['name']);
            $storeInfo->setPinyin( array($pinyin_full, $pinyin_first) );
            $dm->persist($storeInfo);
            $dm->flush();
//            $count ++;
//            if( $count >= 10 ) {
//                break;
//            }
        }
    }

    /**
     * 按照更新时间，增量导入到MongoDB
     */
    public function updateStore()
    {

    }

    protected function insertStoreIntoMongo()
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $stores = $dm->getRepository('DWDDataBundle:Store')
            ->findAll();

        Pinyin::set('accent', false);
        Pinyin::set('delimiter', '');
        foreach( $stores as $store ) {
            $name = $store->getName();
            $pinyin_full = Pinyin::trans($name);
            $pinyin_full = preg_replace('~[^\p{Han}0-9a-zA-Z]~u', '', $pinyin_full);
            $pinyin_first = Pinyin::letter($name);
            $store->setPinyin( array( $pinyin_full, $pinyin_first ) );
            $dm->flush();
        }
    }

    /**
     * 利用mysqli查询MySQL，返回数组
     * @param $query
     * @param null $conn
     * @return array
     */
    protected function getResultByQuery($query, $conn = null)
    {

        if (is_null($conn)) {
            $conn = $this->old_db_connection;
        }

        $result = $conn->query($query);

        $this->writeln("SQL on Old Database:\t<info>" . $query . "</info>\tAffected Rows:\t" . mysqli_affected_rows($conn));

        $return = array();
        if ($result) {
            while ($myrow = $result->fetch_array(MYSQLI_ASSOC)) {
                $return[] = $myrow;
            }
        }

        return $return;
    }

    /**
     * 利用mysqli连接MySQL
     * @param $host
     * @param $user
     * @param $password
     * @param $db
     */
    protected function connectDB($host, $user, $password, $db)
    {
        $this->old_db_connection = mysqli_connect($host, $user, $password, $db);

        if (!$this->old_db_connection) {
            $this->writeln('<error>Error: Can not connect to the old database</error>');
        }
        $this->old_db_connection->query('set names utf8');
    }

    protected function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 1) . ' ' . $unit[$i];
    }

    protected function writeln($text)
    {
        $prefix = '[' . (new \DateTime('now'))->format('Y-m-d H:i:s') . "][" . $this->convert(memory_get_usage(true)) . "]\t";
        $this->output->writeln($prefix . $text);
    }
}