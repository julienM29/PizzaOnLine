<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeFormType;
use App\Repository\EtatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: '_commande')]
    public function index(EtatRepository $etatRepository): Response
    {
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $commande = new Commande();
        $commande->setEtat($etatCreer);
        $commandeForm = $this->createForm(CommandeFormType::class, $commande);
        return $this->render('commande/index.html.twig', compact('commandeForm'));
    }
}
