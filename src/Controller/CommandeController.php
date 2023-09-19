<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: '_commande')]
    public function index(): Response
    {
        $commande = new Commande();
        $commandeForm = $this->createForm(CommandeFormType::class, $commande);
        return $this->render('commande/index.html.twig', compact('commandeForm'));
    }
}
