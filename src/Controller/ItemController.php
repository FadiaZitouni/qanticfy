<?php

namespace App\Controller;

use App\Entity\Item;
use App\ServiceApi\CallInternshipApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    //createItem is a function to save  items from API in database
    public function createItem(EntityManagerInterface $entityManager,CallInternshipApi $callInternshipApi): Response
    {
        $getItemsApi = $callInternshipApi->getItemsApi();
        if(is_array($getItemsApi)){
            for ($i=0;$i<count($getItemsApi);$i++) {
                $find = $entityManager->getRepository(Item::class)->findOneBy(['origin_id' => $getItemsApi[$i]["Item"]]);
                if(!$find){
                    $item = new Item();
                    $item->setOriginId($getItemsApi[$i]["Item"]);
                    $item->setItemDescription($getItemsApi[$i]["ItemDescription"]);
                    $item->setUnitCode($getItemsApi[$i]["UnitCode"]);
                    $item->setUnitDescription($getItemsApi[$i]["UnitDescription"]);
                    $item->setUnitPrice($getItemsApi[$i]["UnitPrice"]);
                    $item->setVatPercentage($getItemsApi[$i]["VATPercentage"]);
                    // tell Doctrine you want to (eventually) save the Item (no queries yet)
                    $entityManager->persist($item);
                }
            }
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            $msg = 'True';
        }
        else{
            $msg = $getItemsApi;
        }
        return new Response($msg);
    }
}
