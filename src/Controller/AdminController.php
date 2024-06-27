<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardTransaction;
use App\Entity\Deposit;
use App\Entity\Investment;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Withdrawal;
use App\Entity\Wallet;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    #[Route('/', name: 'admin')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();
        $deposits = $doctrine->getRepository(Deposit::class)->findBy(["status"=>"pending"]);
        $withdrawals = $doctrine->getRepository(Withdrawal::class)->findBy(['status'=> false]);

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
        ]);
    }

    #[Route('/profile/{id}', name: 'profileview')]
    public function profile(string $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $id = $doctrine->getRepository(User::class)->find($id);
        if(null != $request->get('update')){
            $id->setBalance($request->get('balance'))
               ->setTotaldeposit($request->get('deposit'))
               ->setTotalwithdrawal($request->get('withdrawal'))
               ->setTotalinterests($request->get('interest'));
    
            $em->persist($id);
            $em->flush();
           

           noty()->addSuccess("profile was updated successfully");
            $u = $id->getId();
            return $this->redirectToRoute("admin");

        }
        if(null != $request->get('activate')){
            $id->setStatus(!$id->isStatus());
            $em->persist($id);
            $em->flush();
            if(!$id->isStatus()){
                $message = "Dear ". $id->getFirstname() . " " . $id->getLastname() . " Due to Some Valid Reasons, Your Digihost Account Was De-Activated, please contact our support team for further investigation";
                $this->emailSender->sendTwigEmail($id->getEmail(), "Account De-Activated", "emails/noti.html.twig", [
                    "title" => "Digihost Account De-Activated",
                    "message" => $message,
                ]);
            }
           

           noty()->addSuccess("profile was activated successfully");
            $u = $id->getId();
            return $this->redirectToRoute("admin");

        }
        if(null != $request->get('delete')){
            $em->remove($id);
            $em->flush();

            $this->addFlash(
               'success',
               'user successfully deleted'
            );
            return $this->redirectToRoute('admin');
        }

        
        return $this->render('admin/profile.html.twig', [
            'user' => $id
        ]);
    }


    #[Route('/depositlist', name: 'depositlist')]
    public function depositlist(ManagerRegistry $doctrine, Request $request, EmailSender $emailSender): Response
    {
        $withdrawals = $doctrine->getRepository(Deposit::class)->findBy(["status"=>"pending"]);

        $em = $doctrine->getManager();
        if(null != $request->get('approve')){
            $transaction = $doctrine->getRepository(Deposit::class)->find($request->get('id'));
            $transaction->setStatus('approved');
            //->setCreated(new \DateTime($request->get("date")));
            $tuser =  $transaction->getUser();
            $tuser->setTotaldeposit($tuser->getTotaldeposit() + $transaction->getAmount());
            $em->persist($tuser);
            $em->persist($transaction);
            
            $amount = $transaction->getAmount();
            $em->flush();

            
            // $this->emailSender->sendTwigEmail($tuser->getEmail(), "Transaction Complete", "emails/noti.html.twig", [
            //     "title" => "Transaction Complete",
            //     "message" => $tuser->getFirstname() . " " . $tuser->getLastname() . ",  your transaction of $". $amount . " has been approved successfully",
            // ]);
            noty()->addSuccess("Transaction was successfuly approved");
            return $this->redirectToRoute('withdrawallist');
            
        } 
        if(null != $request->get('delete')){
            $transaction = $doctrine->getRepository(Deposit::class)->find($request->get('id')); 
            $transaction->setStatus('cancelled');
            //->setDate(new \DateTime($request->get("date")));
            $em->persist($transaction);

            

            noty()->addError("Transaction was successfuly declined");
            
            $em->flush();

            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/deposits.html.twig', [
            'deposits' => $withdrawals
        ]);
    }

    #[Route('/withdrawallist', name: 'withdrawallist')]
    public function withdrawals(ManagerRegistry $doctrine, Request $request, EmailSender $emailSender): Response
    {
        $withdrawals = $doctrine->getRepository(Withdrawal::class)->findBy(["status"=>"pending"]);

        $em = $doctrine->getManager();
        if(null != $request->get('approve')){
            $transaction = $doctrine->getRepository(Withdrawal::class)->find($request->get('id'));
            $transaction->setStatus('approved');
            //->setCreated(new \DateTime($request->get("date")));
            $tuser =  $transaction->getUser();
            $tuser->setTotalwithdrawal($tuser->getTotalwithdrawal() + $transaction->getAmount());
            $em->persist($tuser);
            $em->persist($transaction);
            
            $amount = $transaction->getAmount();
            $em->flush();

            
            // $this->emailSender->sendTwigEmail($tuser->getEmail(), "Transaction Complete", "emails/noti.html.twig", [
            //     "title" => "Transaction Complete",
            //     "message" => $tuser->getFirstname() . " " . $tuser->getLastname() . ",  your transaction of $". $amount . " has been approved successfully",
            // ]);
            noty()->addSuccess("Transaction was successfuly approved");
            return $this->redirectToRoute('withdrawallist');
            
        } 
        if(null != $request->get('delete')){
            $transaction = $doctrine->getRepository(Withdrawal::class)->find($request->get('id')); 
            $transaction->setStatus('cancelled');
            //->setDate(new \DateTime($request->get("date")));
            $em->persist($transaction);

            

            noty()->addError("Transaction was successfuly declined");
            
            $em->flush();

            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/withdrawals.html.twig', [
            'withdrawals' => $withdrawals
        ]);
    }


    
    #[Route('/wallet', name: 'wallet')]
    public function wallet(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        $em = $doctrine->getManager();
        $wallet = $doctrine->getRepository(Wallet::class)->find(1);

        if(null != $request->get('update')){
           $wallet->setBtc($request->get('btc'))
                  ->setEth($request->get('eth'))
                  ->setUsdt($request->get('usdt'));


           
            $em->persist($wallet);

            

            noty()->addSuccess("Wallet Addresses were successfuly Updated");
            
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        
        
        return $this->render('admin/wallet.twig', [
          'wallet' => $wallet 
        ]);
    }


}
