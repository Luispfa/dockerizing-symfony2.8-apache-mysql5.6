<?php

namespace Lugh\WebAppBundle\Repository;
use Doctrine\ORM\EntityRepository;

class CustomRepository extends EntityRepository{
    
    public function findByNot( array $criteria, array $orderBy = null, $limit = null, $offset = null )
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $qb->select( 'entity' )
            ->from( $this->getEntityName(), 'entity' );
        
        foreach ( $criteria as $field => $value ) {
            $value = strpos($value, '-') != false || (strpos($value, '.') != false && gettype($value) == 'string') ? "'" . addcslashes($value, "\\')_%" ) . "'" : $value;
            $qb->andWhere( $expr->neq( 'entity.' . $field, $value ) );
        }

        if ( $orderBy ) {

            foreach ( $orderBy as $field => $order ) {
                
                $qb->addOrderBy( 'entity.' . $field, $order );
            }
        }

        if ( $limit )
            $qb->setMaxResults( $limit );

        if ( $offset )
            $qb->setFirstResult( $offset );
        
        return $qb->getQuery()
            ->getResult();
    }
    
    public function findByYesNot( array $yescriteria, array $criteria, array $orderBy = null, $limit = null, $offset = null )
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $qb->select( 'entity' )
            ->from( $this->getEntityName(), 'entity' );
        
        foreach ( $yescriteria as $field => $value ) {
            $value = strpos($value, '-') != false ? "'" . addcslashes($value, "\\')_%" ) . "'" : $value;
            $qb->andWhere( $expr->eq( 'entity.' . $field, $value ) );
        }
        
        foreach ( $criteria as $field => $value ) {
            $value = strpos($value, '-') != false ? "'" . addcslashes($value, "\\')_%" ) . "'" : $value;
            $qb->andWhere( $expr->neq( 'entity.' . $field, $value ) );
        }

        if ( $orderBy ) {

            foreach ( $orderBy as $field => $order ) {

                $qb->addOrderBy( 'entity.' . $field, $order );
            }
        }

        if ( $limit )
            $qb->setMaxResults( $limit );

        if ( $offset )
            $qb->setFirstResult( $offset );
        return $qb->getQuery()
            ->getResult();
    }
    
     public function findByGroup( array $criteria, array $orderBy = null, $group = null, $limit = null, $offset = null )
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $qb->select( 'entity' )
            ->from( $this->getEntityName(), 'entity' );

        foreach ( $criteria as $field => $value ) {
            $value = strpos($value, '-') != false ? "'" . addcslashes($value, "\\')_%" ) . "'" : $value;
            $qb->andWhere( $expr->eq( 'entity.' . $field, $value )  );
        }
        
        if ( $orderBy ) {

            foreach ( $orderBy as $field => $order ) {

                $qb->addOrderBy( 'entity.' . $field, $order );
            }
        }
        
        if ( $group ) {

            foreach ( $group as $gr ) {

                $qb->addGroupBy('entity.' . $gr);
            }
        }

        if ( $limit )
            $qb->setMaxResults( $limit );

        if ( $offset )
            $qb->setFirstResult( $offset );

        return $qb->getQuery()
            ->getResult();
    }
}
