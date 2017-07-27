<?php

namespace WidgetsNL\DoctrineEncryptionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use WidgetsNL\DoctrineEncryptionBundle\DependencyInjection\WidgetsNLExtension;

class WidgetsNLDoctrineEncryptionBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WidgetsNLExtension();
    }
}
