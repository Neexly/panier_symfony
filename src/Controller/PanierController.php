<?php

namespace App\Controller;


use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Form\PanierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    /** 
     * @Route("/", name="home")
     */
    public function index(Request $request, EntityManagerInterface $entityManager,  TranslatorInterface $translator)
    {
        $panierRepository = $this->getDoctrine()->getRepository(Panier::class)->findAll();
        $produitRepository = $this->getDoctrine()->getRepository(Produit::class)->findAll();  

        return $this->render('panier/panier.html.twig', [
            'panier' => $panierRepository,
        ]);
    }

    /**
     *@Route("/produits", name="produits")
     */
    public function produits(Request $request, EntityManagerInterface $entityManager){
        
        $panierRepository = $this->getDoctrine()->getRepository(Panier::class)->findAll();
        $produitRepository = $this->getDoctrine()->getRepository(Produit::class)->findAll();  

        $produit = new Produit();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $produit = $form->getData();

            
            $image = $produit->getPhoto();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_files') ,
            $imageName);
            $produit->setPhoto($imageName);

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute("produits");
        }

        return $this->render('panier/produits.html.twig', [
            'produits' => $produitRepository,
            'panier' => $panierRepository,
            'produitForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/SingleProduit/{id}", name="singleProduit")
     */
    public function singleProduit($id, Request $request, EntityManagerInterface $entityManager){

        $panier = new Panier();
        
        $produit = $this-> getDoctrine()
            ->getRepository(Produit::class)
            ->find($id);

        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $panier = $form->getData();
            $panier->setDateAjout(new \DateTime())
            ->setEtat(false)
            ->setProduit($produit);
        

            $entityManager->persist($panier);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('panier/singleProduit.html.twig',[
            'produit' => $produit,
            'panierForm' => $form->createView(),
            'panier' => $panier,
        ]);

    }


    /**
     * @Route("/deleteProduit/{id}", name="deleteProduit")
     */
    public function deleteProduit($id, EntityManagerInterface $entityManager){
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id); 
        
        $panier = $this->getDoctrine()->getRepository(Panier::class)->findBy(["produit" => $produit]);

        foreach ($panier as $panierline) {
            $entityManager->remove($panierline);
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute("produits");
        

    }

    /**
     * @Route("/deletePanierLine/{id}", name="deletePanierLine")
     */
    public function deletePanierLine($id, EntityManagerInterface $entityManager){
        $panierLine = $this->getDoctrine()->getRepository(Panier::class)->find($id);
        $panierLine->deleteFile();

        $entityManager->remove($panierLine);
        $entityManager->flush();

        return $this->redirectToRoute("home");
        

    }

        /**
     * @Route("/deletePanier", name="deletePanier")
     */
    public function deletePanier(EntityManagerInterface $entityManager){
        $panierLine = $this->getDoctrine()->getRepository(Panier::class)->findAll();
        $produit = $this->getDoctrine()->getRepository(Produit::class)->findAll();

        foreach ($panierLine as $line) {
        $entityManager->remove($line);
     }
        $entityManager->flush();
        return $this->redirectToRoute("home");
           

    }

    }
