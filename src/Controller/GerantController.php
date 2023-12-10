<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Produit;
use App\Form\IngredientFormType;
use App\Form\ProduitFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\IngredientRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class GerantController extends AbstractController
{
    #[Route('/gerant', name: '_gerant')]
    public function index(CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);
        return $this->render('gerant/index.html.twig', [
            'controller_name' => 'GerantController',
            'prixDuPanier' => $prixDuPanier,
            'detailsCommandePanier' => $detailsCommandePanier,
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
    public function creationProduit(Request $requete,CommandeRepository $commandeRepository, SluggerInterface $slugger, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $produitForm = $this->createForm(ProduitFormType::class, $produit);
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);


        $produitForm->handleRequest($requete);

        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            $file = $produitForm->get('urlImage')->getData();
            if($file){
              $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
              $safeFilename = $slugger->slug($originalFileName);
              $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                  $file->move(
                      $this->getParameter('uploaded_images_directory'),
                      $newFilename
                  );

            }
            $produit->setUrlImage($newFilename);
            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }

        return $this->render('gerant/creationProduit.html.twig',compact('produitForm', 'detailsCommandePanier', 'prixDuPanier'));
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
    public function prixDuPanier ($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier){
        $prixDuPanier = 0; // Instancie une variable de prix de panier à 0 qui sera envoyé au twig
        $tailleLarge = $tailleProduitRepository->findOneBy(array('id' => 2)); // récupère la taille large pour une condition (prix)
        if ($derniereCommande != null) { // Si il y a une dernière commande
            $etatDerniereCommande = $derniereCommande->getEtat();
            if ($etatDerniereCommande->getId() == 1) { // Si elle est a l'état créé
                foreach ($detailsCommandePanier as $detail) {
                    $idProduit = $detail->getProduit()->getId();
                    $pizza = $produitRepository->findOneBy(array('id' => $idProduit));
                    if ($detail->getTaille() === $tailleLarge) { // Si il y a une taille large on change le prix
                        $prixDuDetail = 5 + ($pizza->getPrix() * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail); // Total du panier
                }
            } }
        return $prixDuPanier;
    }
}
