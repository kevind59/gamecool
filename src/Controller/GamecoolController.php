<?php

namespace App\Controller;

use App\Entity\Jeuxvideo;
use App\Form\GamecoolType;
use App\Repository\JeuxvideoRepository;
use App\Service\FileService;
use App\Service\StringService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GamecoolController extends AbstractController
{
    /**
     * @Route("/", name="gamecools.index")
     */
    public function index(JeuxvideoRepository $jeuxvideoRepository):Response
    {
    	

    	$results = $jeuxvideoRepository->findAll();

        return $this->render('gamecool/index.html.twig', [
			'results' => $results
        ]);
    }

    /**
     * @Route("/product/{id}", name="product.details")
     */
    public function details(int $id, JeuxvideoRepository $jeuxvideoRepository):Response
    {
    	// find : sélection d'une entité par son identifiant
	    $result = $jeuxvideoRepository->find($id);
	    // dd($result);
		return $this->render('gamecool/details.html.twig', [
			'result' => $result
		]);
    }

    /**
     * @Route("/products/form", name="product.form")
     * @Route("/products/update/{id}", name="product.update")
     */
	public function form(Request $request, ObjectManager $objectManager, int $id = null, JeuxvideoRepository $jeuxvideoRepository, StringService $stringService, FileService $fileService):Response
	{
		/*
		 * affichage d'un formulaire
		 *   nécessite 1 instance et de l'espace de noms de la classe de formulaire
		 *   createForm: instancier une classe de formulaire
		 *   handleRequest: récupération de la saisie dans le $_POST
		 * */
		/*if($id){
			$entity = $productRepository->find($id);
		} else {
			$entity = new Product();
		}*/
		// si l'id n'est pas vide
		$entity = $id ? $jeuxvideoRepository->find($id) : new Jeuxvideo();
		$type = GamecoolType::class; // renvoie le namespace de la classe

		// ajout d'une propriété dynamique (lors de l'exécution) pour stocker le nom de l'image
		$entity->prevImage = $entity->getImage();

		//dd($entity);
		$form = $this->createForm($type, $entity);
		$form->handleRequest($request);

		/*
		 * formulaire valide
		 *  isValid : formulaire valide
		 *  isSubmitted: formulaire soumis
		 */
		if($form->isSubmitted() && $form->isValid()){
			/*
			 * uniquement pour les champs de type file, supprimer les types dans les getters/setters
			 * récupération de l'entité liée au formulaire
			 *
			 * transfert d'image
			 *   sécurité : renommer les fichiers
			 *   logique :
			 *      insertion dans la bdd
			 *          champ file doit être obligatoire
			 *          transfert d'image
			 *      mise à jour
			 *          champ file est optionnel
			 *          sélection d'un nouveau fichier
			 *              transfert du nouveau fichier
			 *              suppression de l'ancien fichier
			 *          pas de sélection
			 *              conserver l'ancien fichier
			 *  tester l'identifiant pour déterminer la requête sql
			 *      null: insertion
			 *      pas null: mise à jour
			 */
			// si l'dentifiant de l'entité est vide
			if(!$entity->getId()){
				/*
				 * random_bytes : octets binaires aléatoires
				 *      longueur est mutiplié par 2
				 * bin2hex: convertir du binaire vers l'héxadécimal
				 *
				 * UploadedFile : méthodes à utiliser
				 *      guessExtension: récupérer l'extension
				 *      move: transfert du fichier
				 *          cibler le dossier "public"
				 *          2 paramètres :
				 *              dossier de destination
				 *              nom du fichier
				 * */
				/*
				 * avant la création des services
				 *  $imageName = bin2hex(random_bytes(16));
				 *  $extension = $uploadedFile->guessExtension();
				 *  $uploadedFile->move('img/', "$imageName.$extension");
				 * */
				$imageName = $stringService->generateToken(16);
				$uploadedFile = $entity->getImage(); // renvoie un objet UploadedFile
				$extension = $fileService->getExtension($uploadedFile);
				$fileService->upload($uploadedFile, 'img/', "$imageName.$extension");

				// mise à jour de la propriété image avec le nouveau nom de l'image
				$entity->setImage("$imageName.$extension");
				//dd($entity, $extension);
			}
			// si l'entité est mise à jour et qu'une image n'a pas été sélectionnée
			elseif($entity->getId() && !$entity->getImage()){
				// récupération de la propriété dynamique prevImage pour remplir la propriété image
				$entity->setImage( $entity->prevImage );
				//dd($entity);
			}
			// si l'entité est mise à jour et qu'une image a été sélectionnée
			elseif($entity->getId() && $entity->getImage()){
				// unlink : suppression de l'ancienne image
				// avant la création des services : unlink("img/{$entity->prevImage}");
				$fileService->delete('img', $entity->prevImage);

				// transfert de la nouvelle image
				/*
				 * avant la création des services
					$imageName = bin2hex(random_bytes(16));
					$uploadedFile = $entity->getImage();
					$extension = $uploadedFile->guessExtension();
					$uploadedFile->move('img/', "$imageName.$extension");
				 * */
				$imageName = $stringService->generateToken(16);
				$uploadedFile = $entity->getImage(); // renvoie un objet UploadedFile
				$extension = $fileService->getExtension($uploadedFile);
				$fileService->upload($uploadedFile, 'img/', "$imageName.$extension");

				$entity->setImage("$imageName.$extension");
			}

			/*
			 * message flash : message en session supprimé après l'affichage
			 * addFlash : 2 paramètres
			 *    clé de l'entrée dans la session
			 *    valeur
			 * */
			$message = $entity->getId() ? "Le produit a été modifié" : "Le produit a été ajouté";
			$this->addFlash('notice', $message);

			/* ObjectManager permet de gérer les entités (insert, delete, update)
			 *    persist(): mettre en file d'attente la requête SQL
			 *    flush(): exécuter les requêtes SQL en file d'attente
			 */
			$objectManager->persist($entity);
			$objectManager->flush();

			// redirectToRoute : redirection
			return $this->redirectToRoute('gamecool.index');
		}

		// createView : convertir la classe de formulaire en champs de saisie HTML
		return $this->render('gamecool/form.html.twig', [
			'form' => $form->createView()
		]);
	}

	// suppression d'un produit
	/**
	 * @Route("/gamecools/delete/{id}", name="gamecool.delete")
	 */
	public function delete(int $id, JeuxvideoRepository $jeuxvideoRepository, ObjectManager $objectManager):Response
	{
		// sélection de l'entité par son identifiant
		$entity = $jeuxvideoRepository->find($id);

		// suppression de l'entité
		$objectManager->remove($entity);
		$objectManager->flush();

		// suppression de l'image
		unlink("img/{$entity->getImage()}");

		// message
		$this->addFlash('notice', "Le produit a été supprimé");

		// redirection
		return $this->redirectToRoute('gamecools.index');
	}


}