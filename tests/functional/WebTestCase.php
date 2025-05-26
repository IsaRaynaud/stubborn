<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class WebTestCase extends BaseWebTestCase
{
    use ResetDatabase;
    use Factories;
    use MailerAssertionsTrait;
}
