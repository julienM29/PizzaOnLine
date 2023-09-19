<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: '_accueil')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $pizzas = $produitRepository->findAll();

        return $this->render('accueil/index.html.twig',compact('pizzas'));
    }
}
