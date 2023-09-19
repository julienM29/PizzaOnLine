<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Produit;
use App\Form\IngredientFormType;
use App\Form\ProduitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GerantController extends AbstractController
{
    #[Route('/gerant', name: '_gerant')]
    public function index(): Response
    {
        return $this->render('gerant/index.html.twig', [
            'controller_name' => 'GerantController',
        ]);
    }
    #[Route('/ingredient', name: '_ingredient')]
    public function CreationIngredient(Request $requete, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();
        $ingredientForm = $this->createForm(IngredientFormType::class, $ingredient);
        $ingredientForm->handleRequest($requete);

        if ($ingredientForm->isSubmitted() && $ingredientForm->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }

        return $this->render('gerant/creationIngredient.html.twig',compact('ingredientForm'));
    }
    #[Route('/produit', name: '_produit')]
    public function CreationProduit(Request $requete, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $produitForm = $this->createForm(ProduitFormType::class, $produit);
        $produitForm->handleRequest($requete);

        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }

        return $this->render('gerant/creationProduit.html.twig',compact('produitForm'));
    }
}
