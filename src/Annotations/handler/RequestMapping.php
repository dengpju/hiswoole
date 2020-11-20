<?php
namespace Src\Annotations\handler;

use Src\Annotations\RequestMapping;
use Src\Core\BeanFactory;

return [
    RequestMapping::class => function(\ReflectionMethod $method, $instance, $self){
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
                return $method->invokeArgs($instance,$inputParams);
            });
        return $instance;
    },
];