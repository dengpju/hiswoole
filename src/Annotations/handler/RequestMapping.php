<?php
namespace Src\Annotations\handler;

use Src\Annotations\RequestMapping;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;

return [
    RequestMapping::class => function(\ReflectionMethod $method, object $instance,RequestMapping $self){
        $routerCollectr = BeanFactory::getBean('RouterCollector');
        $routerCollectr->addRoute(count($self->method)>0?$self->method:["GET"],
            $self->value, function ($parms, $extParms) use ($instance, $method){
            $inputParams = [];
                $refParams = $method->getParameters();
                foreach ($refParams as $refParam) {
                    if (isset($parms[$refParam->getName()])){
                        $inputParams[] = $parms[$refParam->getName()];
                    }else{
                        foreach ($extParms as $extParm){
                            if ($refParam->getClass()->isInstance($extParm)){
                                $inputParams[] = $extParm;
                                goto end;
                            }
                        }
                        $inputParams[] = false;
                    }
                    end:
                }
                $dCollector = BeanFactory::getBean(DecoratorCollector::class);
                return $dCollector->exec($method, $instance, $inputParams);
                //return $method->invokeArgs($instance,$inputParams);
            });
        return $instance;
    },
];