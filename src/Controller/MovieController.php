<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Description of MovieController
 *
 * @author swillemetz
 * @route("/api", name="api_")
 */
class MovieController extends \FOS\RestBundle\Controller\FOSRestController
{
    
    /**
     * @Rest\Get("/movies")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMovieAction()
    {
        $repository = $this->getDoctrine()->getRepository(\App\Entity\Movie::class);
        $movies = $repository->findAll();
        return $this->handleView($this->view($movies));
    }
    
    /**
     * @Rest\Post("/movies")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postMovieAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $movie = new \App\Entity\Movie();
        $data = json_decode($request->getContent(), true);
        $movie->setDescription($data['description']);
        $movie->setName($data['name']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($movie);
        $em->flush();
        $this->handleView($this->view(
            ['status' => 'ok'],
            \Symfony\Component\HttpFoundation\Response::HTTP_CREATED
        ));
    }
}
