<?php

namespace App\Controller\Admin;

use App\Entity\Photo;
use App\Entity\Voyage;
use App\Form\VoyageType;
use App\Repository\VoyageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/voyage")
 */
class VoyageController extends AbstractController
{
    /**
     * @Route("/", name="voyage_index", methods={"GET"})
     */
    public function index(VoyageRepository $voyageRepository): Response
    {
        return $this->render('admin/voyage/index.html.twig', [
            'voyages' => $voyageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="voyage_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $voyage = new Voyage();
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $photos = $form['photos']->getData();

            foreach ($photos as $itemPhoto) {
                $photo = new Photo();
                $photo->setVoyage($voyage);

                $destination = $this->getParameter('voyage_photo_folder');

                $fileName = uniqid() . '.' . $itemPhoto->guessExtension();
                $itemPhoto->move(
                    $destination,
                    $fileName
                );

                $photo->setFilePath($fileName);
                $entityManager->persist($photo);
            }

            $entityManager->persist($voyage);
            
            $entityManager->flush();

            return $this->redirectToRoute('admin_voyage_index');
        }

        return $this->render('admin/voyage/new.html.twig', [
            'voyage' => $voyage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voyage_show", methods={"GET"})
     */
    public function show(Voyage $voyage): Response
    {
        return $this->render('admin/voyage/show.html.twig', [
            'voyage' => $voyage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="voyage_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Voyage $voyage): Response
    {
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_voyage_index');
        }

        return $this->render('admin/voyage/edit.html.twig', [
            'voyage' => $voyage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voyage_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Voyage $voyage): Response
    {
        if ($this->isCsrfTokenValid('delete' . $voyage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $photos = $voyage->getPhotos();
            foreach($photos as $photo) {
                unlink($this->getParameter('voyage_photo_folder') . '/' . $photo->getFilePath());
            }

            $entityManager->remove($voyage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_voyage_index');
    }
}
