<?php

namespace DWD\DataBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

 
class ComplaintRepository extends DocumentRepository
{
 
	public function getAll( $conditions = array(), $options = array() )
	{
		$options['limit'] = isset( $options['limit'] ) ? intval( $options['limit'] ) : 20;
		$options['skip']  = isset( $options['skip'] )  ? intval( $options['skip'] )  :  0;
		return $this->createQueryBuilder()->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
	} 


	public function getCount( $conditions = array() )
	{
		return $this->createQueryBuilder()->hydrate(false)->getQuery()->count();
	}

	public function getUserComplaints( $userId, $options = array() )
	{ 
		$options['limit'] = isset( $options['limit'] ) ? intval( $options['limit'] ) : 20;
		$options['skip']  = isset( $options['skip'] )  ? intval( $options['skip'] )  :  0;
		return $this->createQueryBuilder()->field('userId')->equals(intval($userId))->limit($options['limit'])->skip($options['skip'])->hydrate(false)->getQuery()->toArray();
	}

	public function getUserCount( $userId )
	{
		return $this->createQueryBuilder()->field('userId')->equals(intval($userId))->hydrate(false)->getQuery()->count();
	}

	public function getComplaint( $complaintId )
	{
		return $this->createQueryBuilder()->field('_id')->equals($complaintId)->hydrate(false)->getQuery()->getSingleResult();
	}
}