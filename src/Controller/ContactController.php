<?php

namespace App\Controller;

use App\ServiceApi\CallInternshipApi;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    //createContact is a function to save  contacts from API in database
    public function createContact(EntityManagerInterface $entityManager,CallInternshipApi $callInternshipApi): Response
    {
        $getContactsApi = $callInternshipApi->getDataApi("contacts");
        if(is_array($getContactsApi)){
            if(empty($getContactsApi)){
                $msg = 'List of items is empty';
            }
            else{
                for ($i=0;$i<count($getContactsApi);$i++) {
                    $find = $entityManager->getRepository(Contact::class)->findOneBy(['origin_id' => $getContactsApi[$i]["ID"]]);
                    if(!$find){
                        $contact = new Contact();
                        $contact->setOriginId($getContactsApi[$i]["ID"]);
                        $contact->setAccountName($getContactsApi[$i]["AccountName"]);
                        $contact->setAddressLine1($getContactsApi[$i]["AddressLine1"]);
                        $contact->setAddressLine2($getContactsApi[$i]["AddressLine2"]);
                        $contact->setCity($getContactsApi[$i]["City"]);
                        $contact->setContactName($getContactsApi[$i]["ContactName"]);
                        $contact->setCountry($getContactsApi[$i]["Country"]);
                        $contact->setZipCode($getContactsApi[$i]["ZipCode"]);
                        // tell Doctrine you want to (eventually) save the Product (no queries yet)
                        $entityManager->persist($contact);
                    }
                }
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
                $msg = 'True';
            }

        }
        else{
            $msg = $getContactsApi;
        }
        return new Response($msg);
    }
}
