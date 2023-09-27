<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Produit;
use App\Form\IngredientFormType;
use App\Form\ProduitFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\IngredientRepository;
use App\Repository\ProduitRepository;
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
    ////////////////////////////////////////////////////// CREATION /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/ingredient', name: '_ingredient')]
    public function creationIngredient(Request $requete, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {
        $ingredient = new Ingredient();
        $allIngredient = $ingredientRepository->findAll();
        $count = count($allIngredient) - 1;
        $ingredientForm = $this->createForm(IngredientFormType::class, $ingredient);
        $ingredientForm->handleRequest($requete);

        if ($ingredientForm->isSubmitted() && $ingredientForm->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }

        return $this->render('gerant/creationIngredient.html.twig',compact('ingredientForm', 'allIngredient', 'count'));
    }
    #[Route('/produit', name: '_produit')]
    public function creationProduit(Request $requete, EntityManagerInterface $entityManager): Response
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

    ////////////////////////////////////////////////////// MODIFICATION /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/listeProduit', name: '_listeProduit')]
    public function listeProduit(Request $requete, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
       $allPizzas = $produitRepository->findAll();

        return $this->render('gerant/listeProduit.html.twig',compact('allPizzas'));
    }
    #[Route('/modificationProduit/{id}', name: '_modificationProduit')]
    public function modificationProduit( $id, Request $requete, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        $produitForm = $this->createForm(ProduitFormType::class, $pizza );
        $produitForm->handleRequest($requete);

        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            $entityManager->persist($pizza);
            $entityManager->flush();
            return $this->redirectToRoute('_listeProduit');
        }
        return $this->render('gerant/modificationProduit.html.twig',compact('produitForm', 'pizza'));
    }
    #[Route('/supprimerProduit/{id}', name: '_supprimerProduit')]
    public function suppressionProduit($id, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        if($pizza){
        $entityManager->remove($pizza);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
    }
        return $this->render('gerant/listeProduit.html.twig');
    }
    ////////////////////////////////////////////////////// Gestion /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/gestionDesRoles', name: '_gestionDesRoles')]
    public function gestionDesRoles(CollaborateurRepository $collaborateurRepository, EntityManagerInterface $entityManager): Response
    {
        $collaborateurs = $collaborateurRepository->findAll();

        return $this->render('gerant/gestionDesRoles.html.twig', compact('collaborateurs'));
    }
}
