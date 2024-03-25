<?php

namespace App\Controller;

use App\Entity\Collaborateur;
use App\Form\RegistrationFormType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $user = new Collaborateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),

        ]);
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
    public function tooltip($commandeRepository,$tailleProduitRepository,$produitRepository){
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommandeTrieesParNomProduit();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);
        $result =[];
        $result =[
            'detailsCommandePanier'=> $detailsCommandePanier,
            'prixDuPanier'=>$prixDuPanier,
        ];
        return $result;
    }
}
