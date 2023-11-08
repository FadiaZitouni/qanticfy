<?php

namespace App\Controller;

use App\Entity\CommandLine;
use App\Entity\Item;
use App\Entity\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CommandLineController extends AbstractController
{
    //createCommandLine is a function to save $salesOrderLines with a foreign key $originId order in database
    public function createCommandLine(EntityManagerInterface $entityManager,Array $salesOrderLines, string $originId): Response
    {
        $commandeLine = new CommandLine();
        $repository = $entityManager->getRepository(Command::class);
        $command = $repository->findOneBy(['origin_id' => $originId]);
        $repository = $entityManager->getRepository(Item::class);
        $item = $repository->findOneBy(['item_description' => $salesOrderLines["ItemDescription"]]);
        $commandeLine->setCommand($command);
        $commandeLine->setItem($item);
        $commandeLine->setAmount($salesOrderLines["Amount"]);
        $commandeLine->setDescription($salesOrderLines["Description"]);
        $commandeLine->setDiscount($salesOrderLines["Discount"]);
        $commandeLine->setQuantity($salesOrderLines["Quantity"]);
        $commandeLine->setVatAmount($salesOrderLines["VATAmount"]);
        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($commandeLine);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        return new Response('True');
    }

}
