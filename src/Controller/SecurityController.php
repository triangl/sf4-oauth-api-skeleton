<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as FOSRest;

/**
 * Description of SecurityController
 *
 * @author swillemetz
 */
class SecurityController extends \FOS\RestBundle\Controller\FOSRestController {

    /**
     *
     * @var \FOS\OAuthServerBundle\Model\ClientManagerInterface
     */
    private $clientManager;

    public function __construct(\FOS\OAuthServerBundle\Model\ClientManagerInterface $clientManager) {
        $this->clientManager = $clientManager;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @FOSRest\Post("/createClient")
     */
    public function authenticationAction(\Symfony\Component\HttpFoundation\Request $request) {
        $data = json_decode($request->getContent(), true);
        if (empty($data['redirect-uri']) || empty($data['grant-type'])) {
            return $this->handleView($this->view($data));
        }
        $clientManager = $this->clientManager;
        /** @var \FOS\OAuthServerBundle\Model\Client $client */
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

}
