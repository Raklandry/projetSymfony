<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Entity\Tache;
use App\Repository\TacheRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/{idTache}", name="app_home", requirements={"idTache"="\d+"})
     */
    public function index(TacheRepository $tacheRepo, $idTache = null): Response
    {
        $taches = $tacheRepo->findAll();

        if(!empty($idTache)){
            $currentTache = $tacheRepo->find($idTache);
        }
        if(empty($currentTache)){
            $currentTache = current($taches);
        }

        return $this->render('home/index.html.twig', [
            'taches' => $taches,
            'currentTache' => $currentTache
        ]);
    }

    /**
     * @Route("/ajout", methods="POST", name="app_ajout")
     */
    public function ajout(EntityManagerInterface $em, Request $request): Response
    {
        $nom = $request->get('nom');

        if(empty($nom)){
            $this->addFlash(
               "warning",
               "Un nom de tâche est obligatoire"
            );
            return $this->redirectToRoute('app_home');
        }

        $tache = new Tache();
        $tache->setNom($nom);

        try{
            $em->persist($tache);
            $em->flush();
    
            $this->addFlash(
                /* alt + 0171 («) et alt + 0187 (») */
               "success",
               "La tâche « $nom » a été créée avec succès"
            );

            return $this->redirectToRoute('app_home');
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash(
               "warning",
               "impossible de créer la tâche « $nom », c'est un doublon"
            );
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/supprimer/{idTache}", name="app_delete", requirements={"idTache"="\d+"})
     */
    public function delete(EntityManagerInterface $em, TacheRepository $tacheRepo, $idTache): Response
    {
        $tache = $tacheRepo->find($idTache);

        if (empty($tache)) {
            $this->addFlash(
               "warning",
               "Impossible de supprimer cette tâche"
            );
            
            return $this->redirectToRoute('app_home');
        } else {
            $em->remove($tache);
            $em->flush();

            $nom = $tache->getNom();

            $this->addFlash(
                /* alt + 0171 («) et alt + 0187 (») */
            "success",
            "La tâche « $nom » a été supprimer avec succès"
            );
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/ajout-listing/{idTache}", name="ajout_listing", methods="POST", requirements={"idTache"="\d+"})
     */
    public function addListing(EntityManagerInterface $em, Request $request, $idTache): Response
    {
        $nom = $request->get('nom');
        $description = $request->get('description');
        $tache = $em->getRepository(Tache::class)->find($idTache);
        if(empty($nom) && empty($description)){
            $this->addFlash(
               "warning",
               "le nom et le déscription de listing est obligatoire"
            );
            return $this->redirectToRoute('app_home');
        }

        $listing = new Listing();
        $listing->setNom($nom);
        $listing->setDescription($description);
        $listing->setTache($tache);

        try{
            $em->persist($listing);
            $em->flush();
    
            $this->addFlash(
                /* alt + 0171 («) et alt + 0187 (») */
               "success",
               "La listing « $nom » a été créée avec succès"
            );

            return $this->redirectToRoute('app_home',[
                'idTache' => $idTache
            ]);
            
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash(
               "warning",
               "impossible de créer la tâche « $nom », c'est un doublon"
            );
        }

        return $this->redirectToRoute('app_home',[
            'idTache' => $idTache
        ]);
    }
}
