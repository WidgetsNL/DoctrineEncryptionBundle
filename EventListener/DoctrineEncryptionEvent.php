<?php

namespace WidgetsNL\DoctrineEncryptionBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\PreFlushEventArgs;
use WidgetsNL\DoctrineEncryptionBundle\Algorithm\Aes;
use WidgetsNL\DoctrineEncryptionBundle\Mapping\Encrypt;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use ReflectionClass;

class DoctrineEncryptionEvent implements EventSubscriber
{
    const ANNOTATION_CLASS = Encrypt::class;

    /**
     * Annotation reader
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annReader;

    /**
     * Algoritm
     */
    private $algoritm;

    public function __construct(Reader $annReader)
    {
        $this->annReader = $annReader;
        $this->algoritm  = new Aes('random');
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preFlush,
            Events::postLoad,
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->decrypt($args->getEntity());
    }

    public function preFlush(PreFlushEventArgs $preFlushEventArgs) {
        $unitOfWork = $preFlushEventArgs->getEntityManager()->getUnitOfWork();
        foreach($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->encrypt($entity);
        }
    }


    private function encrypt($entity)
    {
        if (strstr(get_class($entity), "Proxies")) {
            $class = ClassUtils::getClass($entity);
        } else {
            $class = get_class($entity);
        }
        $reflectionClass = new ReflectionClass($class);
        $fields          = $this->getEncryptProperties($reflectionClass);
        foreach ($fields as $field) {
            $this->encryptField($entity, $field);
        }
    }

    private function decrypt($entity)
    {
        if (strstr(get_class($entity), "Proxies")) {
            $class = ClassUtils::getClass($entity);
        } else {
            $class = get_class($entity);
        }

        $reflectionClass = new ReflectionClass($class);
        $fields          = $this->getEncryptProperties($reflectionClass);
        foreach ($fields as $field) {
            $this->decryptField($entity, $field);
        }

    }

    private function getEncryptProperties(ReflectionClass $reflectionClass)
    {
        $fields     = [];
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $hasAnnotation = $this->annReader->getPropertyAnnotation($property, self::ANNOTATION_CLASS);
            if ($hasAnnotation != null) {
                $methodName = ucfirst($property->getName());
                if ( ! $reflectionClass->hasMethod('get' . $methodName)) {
                    throw new AnnotationException('Can\'t use ' . self::ANNOTATION_CLASS . ' without getter function on ' . $reflectionClass->getName() . ':' . $property->getName());
                }
                if ( ! $reflectionClass->hasMethod('set' . $methodName)) {
                    throw new AnnotationException('Can\'t use ' . self::ANNOTATION_CLASS . ' without setter function on ' . $reflectionClass->getName() . ':' . $property->getName());
                }
                $fields[] = $property;
            }
        }

        return $fields;
    }

    private function decryptField($entity, $field)
    {
        $methodName = ucfirst($field->getName());
        $getter     = 'get' . $methodName;
        $setter     = 'set' . $methodName;
        $current    = $entity->$getter();
        $entity->$setter($this->algoritm->decrypt($current));
    }
    private function encryptField($entity, $field)
    {
        $methodName = ucfirst($field->getName());
        $getter     = 'get' . $methodName;
        $setter     = 'set' . $methodName;
        $current    = $entity->$getter();
        $entity->$setter($this->algoritm->encrypt($current));
    }
}