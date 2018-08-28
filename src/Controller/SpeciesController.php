<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Species;
use App\Helper\AutenticateHelper;
use App\Helper\WeatherHelper;
use Symfony\Component\Translation\TranslatorInterface;


class SpeciesController extends AbstractController
{

    public function insert(Request $request, AutenticateHelper $autenticate, TranslatorInterface $translator)
    {

        try{
            if($autenticate->verify($request->headers->get('authorizationCode'))){
                $entityManager = $this->getDoctrine()->getManager();
                $species = new Species();
                $species->setScientificName($request->get('scientificName'));
                $species->setNotes($request->get('notes'));
                $species->setConservationState($request->get('conservationState'));
                $entityManager->persist($species);
                $entityManager->flush();
                return new JsonResponse(['authorized' => true, 'response' => $translator->trans('insert')]);
            }else{
                return new JsonResponse(['authorized' => false]); 
            }
        }catch(\TypeError | \Doctrine\DBAL\Exception\UniqueConstraintViolationException  $ex){
            return new JsonResponse(['exception' => $ex->getmessage()]);
        }
    }


    public function search(Request $request, AutenticateHelper $autenticate, TranslatorInterface $translator)
    {

        try{
            if($autenticate->verify($request->headers->get('authorizationCode'))){
                $species = $this->getDoctrine()->getRepository(Species::class)->findByScientificName($request->get('scientificName'));
                $lista = array();


                foreach ($species as $specie) {


                    $lista[] = array(
                                    'scientificName' => $specie->getScientificName(), 
                                    'notes' => $specie->getNotes()
                                    );         
                }
                return new JsonResponse(['authorized' => true , 'species' => $lista]);
            }else{
                return new JsonResponse(['authorized' => false, 'response' => $translator->trans('not_authorized')]); 
            }
        }catch(\TypeError $ex){
            return new JsonResponse(['exception' => $ex->getmessage()]);
        }
    }


    public function select(Request $request, AutenticateHelper $autenticate, TranslatorInterface $translator)
    {

        try{
            if($autenticate->verify($request->headers->get('authorizationCode'))){
                $species = $this->getDoctrine()->getRepository(Species::class)->find($request->get('scientificName'));
                
                if($species){
                    $lista = array(
                            'scientificName' => $species->getScientificName(), 
                            'notes' => $species->getNotes(),
                            'conservationState' => $species->getConservationState(),
                            );  
                }else{
                    $lista = NULL;
                }

                return new JsonResponse(['authorized' => true , 'species' => $lista]);
            }else{
                return new JsonResponse(['authorized' => false, 'response' => $translator->trans('not_authorized')]); 
            }
        }catch(\TypeError $ex){
            return new JsonResponse(['exception' => $ex->getmessage()]);
        }
    }


    public function update(Request $request, AutenticateHelper $autenticate, TranslatorInterface $translator)
    {

        try{
            if($autenticate->verify($request->headers->get('authorizationCode'))){
                $entityManager = $this->getDoctrine()->getManager();
                $species = $entityManager->getRepository(Species::class)->find($request->get('scientificName'));

                if(!$species) {
                    throw new \Doctrine\DBAL\Exception\InvalidArgumentException($translator->trans('not_found'));
                }else{
                    $species->setScientificName($request->get('newScientificName'));
                    $species->setNotes($request->get('notes'));
                    $species->setConservationState($request->get('conservationState'));
                    $entityManager->flush();
                  return new JsonResponse(['authorized' => true, 'response' => $translator->trans('update')]);

                }
            }
        }catch(\TypeError |  \Doctrine\DBAL\Exception\UniqueConstraintViolationException | \Doctrine\DBAL\Exception\InvalidArgumentException | \Doctrine\ORM\ORMException $ex){
            return new JsonResponse(['exception' => $ex->getmessage()]);
        }
    }

}