<?php

namespace Ibrows\SimpleCMSBundle\Model;

use Ibrows\SimpleCMSBundle\Entity\ContentInterface;

class ContentManager
{

    protected $em;
    protected $items;

    const GROUP_DELIMITER = '___';
    const LOCALE_DELIMITER = '---';

    public function __construct(\Doctrine\ORM\EntityManager $em, $entitiesToManage)
    {
        $this->em = $em;


        $this->items = array();
        foreach ($entitiesToManage as $key => $val) {
            $this->addEntityType($key, $val['class'], $val['type'], $val['repository'], $val['label']);
        }
    }

    public function addEntityType($id, $class, $formtype=null, $repository=null, $label=null)
    {
        if (!$id || !$class || $class instanceof \Ibrows\SimpleCMSBundle\Entity\ContentInterface) {
            return false;
        }
        if ($label == null) {
            $label = $id;
        }
        if ($repository == null) {
            $repository = $this->em->getRepository($class);
        } else {
            $repository .= 'Repository';
            $repository = new $repository($this->em, $this->em->getClassMetadata($class));
        }
        $this->items[$id] = new ContentManagerItem($class, $repository, $label, $formtype);

        return true;
    }

    public function getClass($type='text')
    {
        return $this->items[$type]->getClass();
    }

    /**
     *
     * @param string $type
     * @return ContentInterface 
     */
    public function getEntity($type='text')
    {
        $class = $this->getClass($type);
        return new $class();
    }

    public function getRepository($type='text')
    {
        if(!key_exists($type, $this->items)){
            throw new \Exception("Type '$type' not found, try: ". implode(',',  array_keys($this->items)));           
        }
        return $this->items[$type]->getRepository();
    }

    public function getFormType($type='text')
    {
        $formtype = $this->items[$type]->getFormType();
        return new $formtype();
    }

    public function getTypes()
    {
        return array_keys($this->items);
    }

    public function getContentModelItems()
    {
        return $this->items;
    }

    /**
     *
     * @param string $type
     * @param string $key
     * @return ContentInterface 
     */
    public function create($type='text', $key=null, $locale=null)
    {
        $class = $this->getClass($type);
        $obj = new $class();
        $obj->setKeyword($key);
        self::setLocale($obj, $locale);
        return $obj;
    }

    /**
     *
     * @param string $type
     * @param string $key
     * @return ContentInterface|null 
     */
    public function find($type='text', $key, $locale=null, $fallback=null)
    {
        if ($fallback == null || $locale == $fallback) {
            return $this->getRepository($type)->findOneBy(array('keyword' => self::generateLocaledKeyword($key, $locale)));
        } else {
            $repo = $this->getRepository($type);
            /* @var $repo \Ibrows\SimpleCMSBundle\Repository\ContentRepository */
            $qb = $repo->createQueryBuilder('scmsc');
            /* @var $qb \Doctrine\ORM\QueryBuilder */
            $qb->where('scmsc.keyword LIKE ?1 OR scmsc.keyword LIKE ?2');
            $qb->orderBy('scmsc.keyword', self::getOrderBy($locale, $fallback));
            $qb->setMaxResults(1);
            $qb->setParameter(1, addcslashes(self::generateLocaledKeyword($key, $locale), '_'));
            $qb->setParameter(2, addcslashes(self::generateLocaledKeyword($key, $fallback), '_'));
            $results = $qb->getQuery()->execute();
            return $results;
        }
    }

    private static function getOrderBy($key, $fallback)
    {
        if (strcasecmp($locale, $fallback) < 0) {
            return 'ASC';
        } else {
            return 'DESC';
        }
    }

    /**
     * @param string $type
     * @param string $groupkey 
     * @return ContentInterface|null 
     */
    public function findAll($type='text', $key, $locale=null, $fallback=null)
    {
        $key = $key;
        $repo = $this->getRepository($type);
        /* @var $repo \Ibrows\SimpleCMSBundle\Repository\ContentRepository */
        $qb = $repo->createQueryBuilder('scmsc');
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb->where('scmsc.keyword LIKE ?1');
        $qb->orderBy('scmsc.keyword', 'ASC');
        $qb->setParameter(1, addcslashes(self::generateLocaledKeyword(self::GROUP_DELIMITER . "{$key}" . self::GROUP_DELIMITER, $locale) . "%", '_'));
        $results = $qb->getQuery()->execute();
        if (sizeof($results) > 0 || $fallback == null || $locale == $fallback) {
            return $results;
        } else {
            return $this->findAll($type, $key, $fallback, null);
        }
    }

    public function getNewGroupKey($key, $all=null, $locale=null)
    {
        if (!$all) {
            $groupkey[1] = $key;
            $groupkey[2] = 1;
        } else {
            end($all);
            $last = current($all);
            $groupkey = $this->splitGroupKey($last->getKeyword());
            $groupkey[2] = intval($groupkey[2]) + 1;
        }

        return $this->generateGroupKey($groupkey[1], $groupkey[2], $locale);
    }

    public function splitGroupKey($groupkey)
    {
        $matches = array();
        preg_match('!' . self::GROUP_DELIMITER . '(.*)' . self::GROUP_DELIMITER . '(.*)' . self::GROUP_DELIMITER . '!u', $groupkey, $matches);
        if (sizeof($matches) == 3) {
            unset($matches[0]);
            return $matches;
        }
        return false;
    }

    private function generateGroupKey($group, $key, $locale=null)
    {
        $key = sprintf('%010s', intval($key));
        $key = self::GROUP_DELIMITER . "{$group}" . self::GROUP_DELIMITER . "{$key}" . self::GROUP_DELIMITER;
        return self::generateLocaledKeyword($key, $locale);
    }

    /**
     * always use first set-keyword, otherwise locale will be overwritten
     * @param string $locale like 'de_DE'
     */
    public static function setLocale(\Ibrows\SimpleCMSBundle\Entity\ContentInterface $content, $locale)
    {
        $content->setKeyword(self::generateLocaledKeyword($content->getKeyword(), $locale));
    }

    /**
     *
     * @return string locale like 'de_DE' 
     */
    public static function getLocale(\Ibrows\SimpleCMSBundle\Entity\ContentInterface $content)
    {
        $arr = explode(self::LOCALE_DELIMITER, $content->getKeyword(), -1);
        if (sizeof($arr) > 0 && $arr[0] != '') {
            return $arr[0];
        } else {
            return null;
        }
    }

    /**
     * Genereate a key with key and locale
     * @param string $key
     * @param string $locale
     * @return string $key
     */
    public static function generateLocaledKeyword($key, $locale)            
    {

        $pos = stripos($key, self::LOCALE_DELIMITER);
        if ($pos === false && $locale != null) {
            $key = $locale . self::LOCALE_DELIMITER . $key;
        } else {
            $pos = strlen(self::LOCALE_DELIMITER)+$pos;
            if ($locale == null) {
                //$key = substr($key, $pos); do nothing
            } else {
                $key = $locale . self::LOCALE_DELIMITER . substr($key, $pos);           
            }
            
        }
        return $key;
    }

}
