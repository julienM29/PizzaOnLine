<?php

namespace App\Controller;

use App\Form\ProfilFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\RoleRepository;
use App\Repository\TailleProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: '_profil')]
    public function index($id, CollaborateurRepository $collaborateurRepository, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);

        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $numero = $user->getTelephone();
        $numeroAvecEspaces = chunk_split($numero, 2, ' ');
        return $this->render('profil/index.html.twig',compact('user','detailsCommandePanier', 'prixDuPanier','numeroAvecEspaces'));
    }
    #[Route('/modificationProfil/{id}', name: '_modificationProfil')]
    public function modificationProfil($id, UserPasswordHasherInterface $userPasswordHasher, CollaborateurRepository $collaborateurRepository, Request $requete, EntityManagerInterface $entityManager): Response
    {
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $profilForm = $this->createForm(ProfilFormType::class, $user);
        $profilForm->handleRequest($requete);

        if($profilForm->isSubmitted() && $profilForm->isValid()){
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $profilForm->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('_profil', ['id' => $id]);
        }
        return $this->render('profil/modificationProfil.html.twig',compact('user', 'profilForm'));
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
