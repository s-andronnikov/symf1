<?php

namespace App\ChainCommandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppChainCommandBundle:Default:index.html.twig');
    }
}
