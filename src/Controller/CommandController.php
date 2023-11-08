<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\Contact;
use App\ServiceApi\CallInternshipApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CommandController extends AbstractController
{
    //show is a function to list the processed orders
    #[Route('/', name: 'app_command_line')]
    public function show(EntityManagerInterface $entityManager,CallInternshipApi $callInternshipApi): Response
    {
        return $this->render('command/index.html.twig', [
            'data' => $this->getData($entityManager),
        ]);
    }
    //createCommand is a function to save orders in database
    public function createCommand(EntityManagerInterface $entityManager,CallInternshipApi $callInternshipApi): Response
    {
        $getOrdersApi = $callInternshipApi->getDataApi("orders");
        if(is_array($getOrdersApi)){
            for ($i=0;$i<count($getOrdersApi);$i++) {
                $find = $entityManager->getRepository(Command::class)->findOneBy(['origin_id' => $getOrdersApi[$i]["OrderID"]]);
                if(!$find){
                    $order = new Command();
                    $repository = $entityManager->getRepository(Contact::class);
                    $contact = $repository->findOneBy(['origin_id' => $getOrdersApi[$i]["DeliverTo"]]);
                    $order->setContact($contact);
                    $order->setAmount($getOrdersApi[$i]["Amount"]);
                    $order->setCurrency($getOrdersApi[$i]["Currency"]);
                    $order->setOriginId($getOrdersApi[$i]["OrderID"]);
                    $order->setOrderNumber($getOrdersApi[$i]["OrderNumber"]);
                    $salesOrderLines = $getOrdersApi[$i]["SalesOrderLines"]["results"];
                    $entityManager->persist($order);
                    $entityManager->flush();
                    for ($j=0;$j<count($salesOrderLines);$j++) {
                    //Call the function createCommandLine from CommandLineController to add the SalesOrderLines in order
                        $reponse = $this->forward('App\Controller\CommandLineController::createCommandLine', [
                            'entityManager'  => $entityManager,
                            'salesOrderLines' => $salesOrderLines[$j],
                            'originId' => $getOrdersApi[$i]["OrderID"],
                        ]);
                    }
                }
            }
            $msg = 'True';
        }
        else{
            $msg = $getOrdersApi;
        }
        return new Response($msg);
    }
    //FlowOrdersToCsv is API to return .csv of processed orders
    #[Route('/flow/orders_to_csv', name: 'project_show', methods:['get'] )]
    public function FlowOrdersToCsv(EntityManagerInterface $entityManager,CallInternshipApi $callInternshipApi): Response
    {
        //call createData to save data in the DataBase
        $reponse = $this->createData($entityManager, $callInternshipApi);
        if($reponse->getContent() !== 'True')
            return $reponse;
        //call getData to get data  of processed orders
        $data = $this->getData($entityManager);
        //call createCSV to generate .csv from $data
        return $this->createCSV($data);
        //return $this->json($data);
    }
//getData is a function to get data from DataBase and return array of processed orders
    public function getData(EntityManagerInterface $entityManager): array
    {
        $commands = $entityManager->getRepository(Command::class)->findAll();
        $data =  [];
        foreach ($commands as $command) {
            $orderNumber = $command->getOrderNumber();
            $contact = $command->getContact();
            $delivery_name = $contact->getContactName();
            $delivery_address = $contact->getAddressLine1();
            $delivery_country = $contact->getCountry();
            $delivery_zipcode = $contact->getZipCode();
            $delivery_city = $contact->getCity();
            $commandLines = $command->getCommandLines();
            $i = 1;
            foreach ($commandLines as $commandLine) {
                $items = $commandLine->getItem();
                $items_count = count($commandLines);
                $item_index = $i;
                $item_id = $items->getOriginId();
                $item_quantity = $commandLine->getQuantity();
                $line_price_excl_vat = $commandLine->getAmount();
                $line_price_incl_vat = $commandLine->getAmount() + $commandLine->getVatAmount();
                $data[] =  [
                    'order' => $orderNumber,
                    'delivery_name' => $delivery_name,
                    'delivery_address' => $delivery_address,
                    'delivery_country' => $delivery_country,
                    'delivery_zipcode' => $delivery_zipcode,
                    'delivery_city' => $delivery_city,
                    'items_count' => $items_count,
                    'item_index' => $item_index,
                    'item_id' => $item_id,
                    'item_quantity' => $item_quantity,
                    'line_price_excl_vat' => $line_price_excl_vat,
                    'line_price_incl_vat' => $line_price_incl_vat
                ];
                $i++;
            }
        }
        return $data;
    }
    //createData is a function  to save contacts, items, command and commande lines in database
    public function createData($entityManager, $callInternshipApi): Response
    {
        $reponse = $this->forward('App\Controller\ContactController::createContact', [
            'entityManager'  => $entityManager,
            'callInternshipApi' => $callInternshipApi
        ]);
        if($reponse->getContent() !== 'True')
            return $reponse;
        $reponse = $this->forward('App\Controller\ItemController::createItem', [
            'entityManager'  => $entityManager,
            'callInternshipApi' => $callInternshipApi
        ]);
        if($reponse->getContent() !== 'True')
            return $reponse;
        $reponse = $this->createCommand($entityManager, $callInternshipApi);
        if($reponse->getContent() !== 'True')
            return $reponse;
        return $reponse;
    }

    // createCSV is function to create .csv of data
    public function createCSV(array  $data): Response
    {
        $encoders = [new CsvEncoder()];
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($data, 'csv');

        $response = new Response($csvContent);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename='.'SalesOrderLines_' . date('Y-m-d_His') . '.csv');
        return $response;
    }

}
