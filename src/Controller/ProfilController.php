<?php

namespace App\Controller;

use App\Form\ProfilFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: '_profil')]
    public function index($id, CollaborateurRepository $collaborateurRepository): Response
    {
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        return $this->render('profil/index.html.twig',compact('user'));
    }
    #[Route('/modificationProfil/{id}', name: '_modificationProfil')]
    public function modificationProfil($id, UserPasswordHasherInterface $userPasswordHasher, RoleRepository $roleRepository, CollaborateurRepository $collaborateurRepository, Request $requete, EntityManagerInterface $entityManager): Response
    {
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $roles = $roleRepository->findAll();
        $user->setPassword('');
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
        return $this->render('profil/modificationProfil.html.twig',compact('user', 'profilForm', 'roles'));
    }
    #[Route('/ajoutRoles/{id}', name: '_ajoutRoles')]
    public function ajoutRoles($id, CollaborateurRepository $collaborateurRepository, RoleRepository $roleRepository, Request $request): Response
    {
        $rolesSelectionnes = $request->request->get('objets');
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        if (!empty($rolesSelectionnes)) {
            foreach ($rolesSelectionnes as $role) {
                $roleAjouter = $roleRepository->findOneBy(['id' => $role]);
                if ($roleAjouter) {
                    $user->addRoles($roleAjouter);
                }
            }
        }


        return $this->render('profil/index.html.twig');
    }
}