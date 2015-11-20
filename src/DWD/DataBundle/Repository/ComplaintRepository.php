<?php

namespace DWD\DataBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

 
class ComplaintRepository extends DocumentRepository
{
 
	public function getAll( $conditions = array(), $options = array() )
	{
		$options['limit'] = isset( $options['limit'] ) ? intval( $options['limit'] ) : 20;
		$options['skip']  = isset( $options['skip'] )  ? intval( $options['skip'] )  :  0;
		$options['sort']  = isset( $options['sort'] )  ? $options['sort']  :  array('_id' => -1);

		if( empty( $conditions ) )
		{
          return $this->createQueryBuilder()->sort( $options['sort'] )->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
		}

		$queryBuilder     = $this->createQueryBuilder();
		foreach ($conditions as $key => $value) {
		    $queryBuilder = $queryBuilder->field($key)->equals($value);
		}
		return $queryBuilder->sort( $options['sort'] )->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
	} 


	public function getCount( $conditions = array() )
	{
		if( empty( $conditions ) )
		{
			return $this->createQueryBuilder()->hydrate(false)->getQuery()->count();
		}

		$queryBuilder     = $this->createQueryBuilder();
		foreach ($conditions as $key => $value) {
		    $queryBuilder = $queryBuilder->field($key)->equals($value);
		}

		return $queryBuilder->hydrate(false)->getQuery()->count();
	}

	public function getUserComplaints( $userId, $options = array() )
	{ 
		$options['limit'] = isset( $options['limit'] ) ? intval( $options['limit'] ) : 20;
		$options['skip']  = isset( $options['skip'] )  ? intval( $options['skip'] )  :  0;
		$options['sort']  = isset( $options['sort'] )  ? $options['sort']  :  array('_id' => -1);

		return $this->createQueryBuilder()->sort( $options['sort'] )->field('userId')->equals(intval($userId))->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
	}

	public function getUserCount( $userId )
	{
		return $this->createQueryBuilder()->field('userId')->equals(intval($userId))->hydrate(false)->getQuery()->count();
	}

	public function getBranchComplaints( $branchId, $options = array() )
	{ 
		$options['limit'] = isset( $options['limit'] ) ? intval( $options['limit'] ) : 20;
		$options['skip']  = isset( $options['skip'] )  ? intval( $options['skip'] )  :  0;
		$options['sort']  = isset( $options['sort'] )  ? $options['sort']  :  array('_id' => -1);
		
		return $this->createQueryBuilder()->sort( $options['sort'] )->field('branchId')->equals(intval($branchId))->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
	}

	public function getBranchCount( $branchId )
	{
		return $this->createQueryBuilder()->field('branchId')->equals(intval($branchId))->hydrate(false)->getQuery()->count();
	}

	public function getComplaint( $complaintId )
	{
		return $this->createQueryBuilder()->field('_id')->equals($complaintId)->hydrate(false)->getQuery()->getSingleResult();
	}
}