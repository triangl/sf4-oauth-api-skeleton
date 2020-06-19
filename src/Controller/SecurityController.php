<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Entity\ClientManager;
use App\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Description of SecurityController
 *
 * @author swillemetz
 */
class SecurityController extends FOSRestController {

    /**
     *
     * @var ClientManagerInterface
     */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager) {
        $this->clientManager = $clientManager;
    }

    /**
     * @param Request $request
     * @return Response
     * @FOSRest\Post("/createClient")
     */
    public function authenticationAction(Request $request) {
        $data = json_decode($request->getContent(), true);
        if (empty($data['redirect-uri']) || empty($data['grant-type'])) {
            return $this->handleView($this->view($data));
        }
        /** @var ClientManager $clientManager */
        $clientManager = $this->clientManager;
        /** @var Client $client */
        $client = $clientManager->createClient();
        $client->setRedirectUris([$data['redirect-uri']]);
        $client->setAllowedGrantTypes([$data['grant-type']]);
        $clientManager->updateClient($client);
        $rows = [
            'client_id' => $client->getPublicId(), 
            'client_secret' => $client->getSecret()
        ];
        return $this->handleView($this->view($rows));
    }
    
    /**
     * @param Request $request
     * @return Response
     * @FOSRest\Delete("/deleteClient")
     */
    public function logoutAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['client_id'])) {
            return $this->handleView($this->view($data));
        }
        
        /** @var ClientManager $clientManager */
        $clientManager = $this->clientManager;
        /** @var Client $client */
        $client = $clientManager->findClientByPublicId($data['client_id']);
        
        $em = $this->getDoctrine()->getManager();
        $accessToken = $em->getRepository(AccessToken::class)->findOneBy(['client' => $client]);
        $refreshToken = $em->getRepository(RefreshToken::class)->findOneBy(['client' => $client]);
        $em->remove($accessToken);
        $em->remove($refreshToken);

        $clientManager->deleteClient($client);
        return $this->handleView($this->view([
            'logout' => true
        ]));
    }

}

